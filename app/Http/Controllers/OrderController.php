<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderReview;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        ], [
            'reason.required' => 'Vui lòng chọn lý do hủy đơn.',
            'reason.in' => 'Vui lòng chọn lý do hủy đơn hợp lệ.',
            'other_reason.required_if' => 'Vui lòng nhập lý do hủy chi tiết.',
            'other_reason.min' => 'Lý do hủy phải có độ dài từ 1-200 ký tự.',
            'other_reason.max' => 'Lý do hủy không được vượt quá 200 ký tự.',
            'other_reason.regex' => 'Lý do hủy không được chỉ gồm khoảng trắng.',
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
            return back()->withErrors(['order' => 'Không thể thực hiện hủy đơn hàng lúc này. Vui lòng thử lại sau.'])
                ->withInput($request->except('_token'));
        }

        return redirect()->route('orders.history')->with('success', 'Hủy đơn hàng thành công.');
    }

    public function reviewForm(Request $request, int $id)
    {
        $order = Order::where('user_id', $request->user()->id)->with('items')->findOrFail($id);
        if ($order->status !== 'completed' || $order->review) {
            return redirect()->route('orders.history')
                ->withErrors(['review' => 'Chỉ đơn đã giao và chưa đánh giá mới được đánh giá.']);
        }

        return view('order.review', compact('order'));
    }

    public function storeReview(Request $request, int $id)
    {
        try {
            $order = Order::where('user_id', $request->user()->id)->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['review' => 'Không thể gửi đánh giá lúc này. Vui lòng thử lại sau.'])
                ->withInput($request->except(['images']));
        }

        if ($order->status !== 'completed' || $order->reviewed_at) {
            return back()->withErrors(['review' => 'Chỉ đơn đã giao và chưa đánh giá mới được đánh giá.']);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required_if:rating,1,2,3', 'nullable', 'string', 'min:1', 'max:500', function ($attribute, $value, $fail) {
                if (is_string($value) && trim($value) === '' && $value !== '') {
                    $fail('Nội dung nhận xét không được chỉ gồm khoảng trắng.');
                    return;
                }

                $blockedWords = ['địt', 'đụ', 'lồn', 'cặc', 'fuck', 'shit', 'bitch'];
                if ($value && Str::contains(Str::lower($value), $blockedWords)) {
                    $fail('Nội dung nhận xét có từ ngữ không phù hợp.');
                }
            }],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Số sao đánh giá phải là số nguyên từ 1 đến 5.',
            'rating.between' => 'Số sao đánh giá phải là số nguyên từ 1 đến 5.',
            'comment.required_if' => 'Vui lòng nhập nội dung nhận xét cho đánh giá dưới 4 sao.',
            'comment.max' => 'Nội dung nhận xét không được vượt quá 500 ký tự.',
            'images.max' => 'Chỉ được tải tối đa 3 ảnh.',
            'images.*.image' => 'Tệp tải lên phải là hình ảnh JPG, JPEG hoặc PNG.',
            'images.*.mimes' => 'Chỉ chấp nhận ảnh JPG, JPEG hoặc PNG.',
            'images.*.max' => 'Mỗi ảnh không được vượt quá 5 MB.',
            'images.*.uploaded' => 'Tải ảnh lên không thành công. Vui lòng chọn lại file.',
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
        abort_unless($request->user()->is_staff, 403); //1
        $validated = $request->validate(['status' => ['required', Rule::in(['confirmed', 'preparing', 'delivering', 'completed'])]], [
            'status.required' => 'Vui lòng chọn trạng thái đơn hàng.',
            'status.in' => 'Trạng thái đơn hàng không hợp lệ.',
        ]);
        $nextStatuses = [
            'pending' => 'confirmed',
            'confirmed' => 'preparing',
            'preparing' => 'delivering',
            'delivering' => 'completed',
        ];

        try { //2
            $order = Order::findOrFail($id); //3
            if ($order->status === 'cancelled') { //4
                return back()->withErrors(['status' => 'Đơn hàng này đã bị khách hàng hủy. Vui lòng làm mới trang.']); //5
            }

            if (($nextStatuses[$order->status] ?? null) !== $validated['status']) { //6
                return back()->withErrors(['status' => 'Không thể bỏ qua bước hoặc cập nhật trạng thái đơn đã kết thúc.']); //7
            }

            $this->transitionOrder($order, $validated['status'], $request->user()->id); //8
        } catch (ModelNotFoundException $exception) {
            throw $exception; //9
        } catch (ValidationException $exception) {
            $currentStatus = Order::whereKey($id)->value('status'); //10
            $message = $currentStatus === 'cancelled'
                ? 'Đơn hàng này đã bị khách hàng hủy. Vui lòng làm mới trang.'
                : 'Trạng thái đơn hàng đã thay đổi. Vui lòng tải lại trang.';

            return back()->withErrors(['status' => $message]); //11
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['status' => 'Không thể cập nhật dữ liệu lúc này, vui lòng thử lại sau']); //12
        }

        return back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.'); //13
    }

    private function transitionOrder(Order $order, string $nextStatus, ?int $actorId, array $extra = []): void
    {
        DB::transaction(function () use ($order, $nextStatus, $actorId, $extra) { //1
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail(); //2
            $allowed = [ //3
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['preparing'],
                'preparing' => ['delivering'],
                'delivering' => ['completed'],
            ];

            if (!in_array($nextStatus, $allowed[$lockedOrder->status] ?? [], true)) { //4
                throw ValidationException::withMessages(['status' => 'Chuyển trạng thái đơn hàng không hợp lệ.']); //5
            }

            $fromStatus = $lockedOrder->status; //6
            $lockedOrder->update(array_merge($extra, ['status' => $nextStatus])); //7
            $lockedOrder->statusHistory()->create([ //8
                'user_id' => $actorId,
                'from_status' => $fromStatus,
                'to_status' => $nextStatus,
            ]);
        });
    }
}