<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderFacade;
use App\Models\Order;
use App\Models\Voucher;
use App\Factories\Voucher\VoucherFactory;
use Carbon\Carbon;
use App\Singletons\SystemConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    protected $orderFacade;

    // Inject Facade Service vào thông qua hàm khởi tạo
    public function __construct(OrderFacade $orderFacade)
    {
        $this->orderFacade = $orderFacade;
    }

    public function index()
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $config = SystemConfig::getInstance();
        $shippingFee = $config->get('shipping_fee', 20000);

        // Lấy danh sách địa chỉ cũ từ bảng user_addresses
        $savedAddresses = auth()->user()->addresses()->orderByDesc('is_default')->latest()->get();

        return view('checkout.index', compact('cart', 'subtotal', 'shippingFee', 'savedAddresses'));
    }

    public function process(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\pL ]+$/u'],
            'phone' => ['required', 'regex:/^0[0-9]{9}$/'],
            'address' => ['required', 'string', 'max:255', Rule::exists('user_addresses', 'address')->where('user_id', $request->user()->id)],
            'time_type' => ['required', 'string', 'max:120'],
            'order_note' => ['nullable', 'string', 'min:5', 'max:200', 'regex:/^[\pL\pN .,-]+$/u'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
        ]);

        $distanceKm = (new \App\Adapters\Map\OpenStreetMapAdapter())->getDistanceInKm(
            'Đại học Thủy Lợi, Đống Đa, Hà Nội',
            $validated['address']
        );
        if ($distanceKm === null || $distanceKm > 15) {
            throw ValidationException::withMessages([
                'address' => $distanceKm === null
                    ? 'Không thể xác định khoảng cách giao hàng. Vui lòng thử lại sau.'
                    : 'Địa chỉ nằm ngoài bán kính giao hàng 15 km.',
            ]);
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += (float) $item['price'] * (int) $item['quantity'];
        }
        if ($subtotal <= 0) {
            throw ValidationException::withMessages(['cart' => 'Giá trị đơn hàng phải lớn hơn 0.']);
        }

        $config = SystemConfig::getInstance();
        $deliveryFee = (float) $config->get('shipping_fee', 20000);

        try {
            $order = DB::transaction(function () use ($request, $validated, $cart, $subtotal, $deliveryFee) {
                $voucher = null;
                $discount = 0;
                if (!empty($validated['voucher_code'])) {
                    $voucher = Voucher::whereRaw('UPPER(code) = ?', [strtoupper($validated['voucher_code'])])->lockForUpdate()->first();
                    if (!$voucher || $voucher->quantity <= 0 || ($voucher->expires_at && Carbon::parse($voucher->expires_at)->isPast()) || $subtotal < max(100000, (float) $voucher->min_order_value)) {
                        throw ValidationException::withMessages(['voucher_code' => 'Voucher không hợp lệ, hết hạn/hết lượt hoặc đơn chưa đạt tối thiểu 100.000đ.']);
                    }
                    $discount = min($subtotal * 0.10, $voucher->max_discount ?: $subtotal * 0.10);
                }

                $order = $this->orderFacade->placeOrder($validated, $cart);
                $order->update([
                    'voucher_id' => $voucher?->id,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'discount_applied' => $discount,
                    'total_amount' => max(0, $subtotal + $deliveryFee - $discount),
                ]);
                if ($voucher) {
                    $voucher->decrement('quantity');
                }

                return $order;
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['checkout' => 'Không thể tạo đơn hàng lúc này. Vui lòng thử lại sau.'])->withInput();
        }

        session()->forget(['cart', 'applied_voucher']);
        return view('checkout.success', compact('order'));
    }

    public function success(Request $request)
    {
        // Lấy dữ liệu thật từ MySQL lên hiển thị ra hóa đơn
        $order = Order::with('items')->where('user_id', auth()->id())->findOrFail($request->id);

        return view('checkout.success', compact('order'));
    }

    public function checkVoucher(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:50']]);
        $cart = session()->get('cart', []);
        $subtotal = collect($cart)->sum(fn ($item) => (float) $item['price'] * (int) $item['quantity']);
        $code = strtoupper($request->input('code'));

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã giảm giá!'
            ]);
        }

        $voucher = Voucher::whereRaw('UPPER(code) = ?', [$code])->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không tồn tại hoặc đã hết hạn!'
            ]);
        }

        if ($subtotal <= 0 || $subtotal < max(100000, (float) $voucher->min_order_value)) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng cần đạt tối thiểu 100.000đ để sử dụng voucher.'
            ]);
        }

        if ($voucher->quantity <= 0 || ($voucher->expires_at && Carbon::parse($voucher->expires_at)->isPast())) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá này đã hết lượt sử dụng!'
            ]);
        }

        try {
            $discountAmount = min($subtotal * 0.10, $voucher->max_discount ?: $subtotal * 0.10);
            $shippingFee = (float) SystemConfig::getInstance()->get('shipping_fee', 20000);
            $finalTotal = max(0, $subtotal + $shippingFee - $discountAmount);

            // Lưu thông tin đồng bộ vào Session phục vụ cho bước Đặt hàng cuối cùng
            session()->put('applied_voucher', [
                'code' => $voucher->code,
                'discount_amount' => $discountAmount
            ]);

            return response()->json([
                'success' => true,
                'discount_amount' => $discountAmount,
                'final_total' => $finalTotal,
                'shipping_fee' => $shippingFee,
                'message' => 'Áp dụng mã giảm giá thành công!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi xử lý mã giảm giá: ' . $e->getMessage()
            ]);
        }
    }

    public function updateAddress(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate(['address' => ['required', 'string', 'min:5', 'max:255']]);
        if ($user) {
            $destinationAddress = trim($validated['address']);

            // --- ÁP DỤNG ADAPTER PATTERN NGAY KHI VỪA CHỌN MAP XONG ---
            // Gọi Adapter đóng vai trò "thông dịch viên" lấy số Km
            $mapAdapter = new \App\Adapters\Map\OpenStreetMapAdapter();
            
            // Tính từ tọa độ quán (VD: ĐH Thủy Lợi) đến địa chỉ khách vừa chọn
            $distanceKm = $mapAdapter->getDistanceInKm('Đại học Thủy Lợi, Đống Đa, Hà Nội', $destinationAddress);

            if ($distanceKm === null) {
                return response()->json(['success' => false, 'message' => 'Không thể xác định khoảng cách địa chỉ. Vui lòng thử lại.'], 422);
            }

            if ($distanceKm > 15) {
                return response()->json(['success' => false, 'message' => 'Địa chỉ nằm ngoài bán kính giao hàng 15 km.'], 422);
            }

            if (!$user->addresses()->where('address', $destinationAddress)->exists() && $user->addresses()->count() >= 10) {
                return response()->json(['success' => false, 'message' => 'Mỗi tài khoản chỉ được lưu tối đa 10 địa chỉ.'], 422);
            }

            // Thêm bản ghi mới vào bảng lịch sử địa chỉ
            $user->addresses()->firstOrCreate(['address' => $destinationAddress], [
                'recipient_name' => $user->name,
                'phone' => $user->phone,
                'is_default' => !$user->addresses()->exists(),
            ]);

            // Trả về cho Frontend cả địa chỉ VÀ số Km đã tính toán qua API
            return response()->json([
                'success' => true, 
                'address' => $destinationAddress,
                'distance_km' => $distanceKm 
            ]);
        }
        
        return response()->json(['success' => false]);
    }
}