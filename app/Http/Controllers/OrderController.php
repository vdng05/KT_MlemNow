<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderReview;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    // Hiển thị danh sách lịch sử đơn hàng
    public function history(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        
        // CHỈ LẤY ĐƠN HÀNG CỦA USER ĐANG ĐĂNG NHẬP
        $query = Order::where('user_id', auth()->id())
                  ->with(['items.food.restaurant', 'review'])
                      ->orderBy('created_at', 'desc');

        // Lọc theo trạng thái (Luồng sự kiện thay thế A1)
        if ($statusFilter === 'delivering') {
            $query->where('status', 'delivering');
        } elseif ($statusFilter === 'completed') {
            $query->where('status', 'completed');
        } elseif ($statusFilter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($statusFilter === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        $orders = $query->get();

        return view('order.history', compact('orders', 'statusFilter'));
    }

    // Xử lý nút "Đặt lại" (Luồng sự kiện thay thế A3)
    // Xử lý đặt lại đơn hàng cũ với đầy đủ thông tin tùy chọn
    public function reorder($id)
    {
        // Eager load sâu từ đơn hàng cũ sang món ăn, nhà hàng và danh sách các topping đã chọn
            $order = Order::where('user_id', auth()->id())->with(['items.food.restaurant', 'items.toppings'])->findOrFail($id);
        $cart = session()->get('cart', []);

        foreach ($order->items as $item) {
            // Tạo mã cart_id duy nhất cho từng thực thể giỏ hàng
            $cartId = md5($item->food_name . time() . rand(1, 100)); 
            
            // Gom toàn bộ danh sách tên topping cũ thành một chuỗi ngăn cách bằng dấu phẩy
            $toppingsString = $item->toppings->pluck('topping_name')->implode(', ');

            $cart[$cartId] = [
                'id'         => $item->food_id,
                'name'       => $item->food_name,
                'price'      => $item->price,
                'quantity'   => $item->quantity,
                'image'      => $item->food ? $item->food->image : null, 
                // Nạp đúng tên nhà hàng sở hữu món ăn
                'restaurant' => $item->food && $item->food->restaurant ? $item->food->restaurant->name : 'Quán ăn đối tác',
                // Đồng bộ chính xác tên Size khớp với key $item['size'] ở View giỏ hàng
                'size'       => $item->size_name, 
                // Đồng bộ chuỗi Topping khớp với key $item['toppings'] ở View giỏ hàng
                'toppings'   => $toppingsString, 
                'note'       => ''
            ];
        }

        // Lưu đè lại mảng dữ liệu hoàn chỉnh vào Session giỏ hàng
        session()->put('cart', $cart);
        
        return redirect()->route('cart.index');
    }

    // Xử lý khi người dùng xác nhận đã nhận hàng
    public function confirmReceived($id)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($id);

        if ($order->status !== 'delivering') {
            return redirect()->back()->withErrors(['order' => 'Chỉ xác nhận đã nhận hàng khi đơn đang được giao.']);
        }

        $this->transitionOrder($order, 'completed', auth()->id());
        
        return redirect()->back()->with('success', 'Xác nhận thành công! Cảm ơn quý khách đã sử dụng dịch vụ của chúng tôi.');
    }
    public function exportPdf($orderId)
    {
        // 1. Tìm đơn hàng
        $order = Order::where('user_id', auth()->id())->findOrFail($orderId);

        // 2. Sử dụng Builder để tạo file PDF
        $pdf = (new \App\Builders\InvoiceBuilder($order))
                    ->addCustomerInfo()
                    ->addFoodItems()
                    ->applyTaxes()
                    ->buildPdf();

        // 3. Trả về file cho trình duyệt tải xuống
        return $pdf->download('Hoa_Don_' . $order->order_code . '.pdf');
    }

    public function cancel(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => ['required', Rule::in([
                'Muốn thay đổi địa chỉ giao hàng',
                'Muốn thay đổi món ăn/số lượng',
                'Đổi ý không muốn mua nữa',
                'Tìm thấy giá tốt hơn',
                'Lý do khác',
            ])],
            'other_reason' => ['required_if:reason,Lý do khác', 'nullable', 'string', 'min:1', 'max:200', 'regex:/.*\S.*/u'],
        ]);

        try {
            DB::transaction(function () use ($request, $id, $validated) {
                $order = Order::where('user_id', $request->user()->id)->lockForUpdate()->findOrFail($id);
                if ($order->status !== 'pending') {
                    throw ValidationException::withMessages(['order' => 'Đơn hàng đã được xác nhận, không thể hủy. Vui lòng liên hệ hỗ trợ.']);
                }

                $reason = $validated['reason'] === 'Lý do khác' ? trim($validated['other_reason']) : $validated['reason'];
                $this->transitionOrder($order, 'cancelled', $request->user()->id, [
                    'cancellation_reason' => $reason,
                    'cancelled_at' => now(),
                ]);

                if ($order->voucher_id) {
                    $order->voucher()->increment('quantity');
                }
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['order' => 'Không thể thực hiện hủy đơn hàng lúc này. Vui lòng thử lại sau.']);
        }

        return redirect()->route('orders.history')->with('success', 'Hủy đơn hàng thành công.');
    }

    public function reviewForm(Request $request, int $id)
    {
        $order = Order::where('user_id', $request->user()->id)->with('items')->findOrFail($id);
        abort_unless($order->status === 'completed' && !$order->review, 404);

        return view('order.review', compact('order'));
    }

    public function storeReview(Request $request, int $id)
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($id);
        if ($order->status !== 'completed' || $order->reviewed_at) {
            return back()->withErrors(['review' => 'Chỉ đơn đã giao và chưa đánh giá mới được đánh giá.']);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required_if:rating,1,2,3', 'nullable', 'string', 'min:1', 'max:500', function ($attribute, $value, $fail) {
                $blockedWords = ['địt', 'đụ', 'lồn', 'cặc', 'fuck', 'shit', 'bitch'];
                if ($value && Str::contains(Str::lower($value), $blockedWords)) {
                    $fail('Nội dung nhận xét có từ ngữ không phù hợp.');
                }
            }],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        try {
            DB::transaction(function () use ($request, $order, $validated) {
                $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                if ($lockedOrder->status !== 'completed' || $lockedOrder->reviewed_at || $lockedOrder->review()->exists()) {
                    throw ValidationException::withMessages(['review' => 'Đơn hàng đã được đánh giá hoặc không còn đủ điều kiện.']);
                }

                $imagePaths = collect($request->file('images', []))
                    ->map(fn ($image) => $image->store('reviews', 'public'))
                    ->all();

                OrderReview::create([
                    'order_id' => $lockedOrder->id,
                    'user_id' => $request->user()->id,
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'] ?? null,
                    'images' => $imagePaths,
                ]);

                $lockedOrder->update(['reviewed_at' => now()]);

                $restaurantIds = $lockedOrder->items()->with('food')->get()
                    ->map(fn ($item) => $item->food?->restaurant_id)
                    ->filter()
                    ->unique();
                foreach ($restaurantIds as $restaurantId) {
                    $average = OrderReview::whereHas('order.items.food', fn ($query) => $query->where('restaurant_id', $restaurantId))->avg('rating');
                    Restaurant::whereKey($restaurantId)->update(['rating' => round((float) $average, 1)]);
                }
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['review' => 'Không thể gửi đánh giá lúc này. Vui lòng thử lại sau.'])->withInput();
        }

        return redirect()->route('orders.history')->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }

    public function staffOrders(Request $request)
    {
        abort_unless($request->user()->is_staff, 403);
        $orders = Order::with('items')->latest()->get();

        return view('order.staff', compact('orders'));
    }

    public function updateStatus(Request $request, int $id)
    {
        abort_unless($request->user()->is_staff, 403);
        $validated = $request->validate(['status' => ['required', Rule::in(['confirmed', 'preparing', 'delivering', 'completed'])]]);
        $order = Order::findOrFail($id);
        $nextStatuses = [
            'pending' => 'confirmed',
            'confirmed' => 'preparing',
            'preparing' => 'delivering',
            'delivering' => 'completed',
        ];

        if (($nextStatuses[$order->status] ?? null) !== $validated['status']) {
            return back()->withErrors(['status' => 'Không thể bỏ qua bước hoặc cập nhật trạng thái đơn đã kết thúc.']);
        }

        try {
            $this->transitionOrder($order, $validated['status'], $request->user()->id);
        } catch (ValidationException $exception) {
            return back()->withErrors(['status' => 'Trạng thái đơn hàng đã thay đổi. Vui lòng tải lại trang.']);
        }

        return back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }

    private function transitionOrder(Order $order, string $nextStatus, ?int $actorId, array $extra = []): void
    {
        DB::transaction(function () use ($order, $nextStatus, $actorId, $extra) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $allowed = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['preparing'],
                'preparing' => ['delivering'],
                'delivering' => ['completed'],
            ];

            if (!in_array($nextStatus, $allowed[$lockedOrder->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'Chuyển trạng thái đơn hàng không hợp lệ.']);
            }

            $fromStatus = $lockedOrder->status;
            $lockedOrder->update(array_merge($extra, ['status' => $nextStatus]));
            $lockedOrder->statusHistory()->create([
                'user_id' => $actorId,
                'from_status' => $fromStatus,
                'to_status' => $nextStatus,
            ]);
        });
    }
}