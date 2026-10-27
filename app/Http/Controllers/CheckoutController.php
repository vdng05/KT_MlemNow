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
        $isBuyNow = session()->get('is_buy_now', false);
        $cart = $isBuyNow ? session()->get('buy_now_item', []) : session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Chưa có món ăn nào để thanh toán.');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $config = SystemConfig::getInstance();
        $shippingFee = $config->get('shipping_fee', 20000);

        // Lấy danh sách địa chỉ cũ từ bảng user_addresses
        $savedAddresses = auth()->user()->addresses()->orderByDesc('is_default')->latest()->get();

        $availableVouchers = Voucher::all();

        return view('checkout.index', compact('cart', 'subtotal', 'shippingFee', 'savedAddresses', 'isBuyNow', 'availableVouchers'));
    }

    public function process(Request $request)
    {
        $isBuyNow = session()->get('is_buy_now', false);
        $cart = $isBuyNow ? session()->get('buy_now_item', []) : session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Chưa có món ăn nào để thanh toán.');
        }

        $validated = $request->validate([
            'name' => [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    if (is_string($value) && trim($value) === '' && $value !== '') {
                        $fail('Vui lòng thêm thông tin người nhận.');
                    }
                },
                function ($attribute, $value, $fail) {
                    if (is_string($value) && preg_match('/[\pN]/u', $value)) {
                        $fail('Họ tên không được chứa chữ số.');
                    }
                },
                'regex:/^[\pL ]+$/u', 'min:2', 'max:50',
            ],
            'phone' => ['required', 'regex:/^0[0-9]{9}$/'],
            'address' => ['required', 'string', 'max:255', Rule::exists('user_addresses', 'address')->where('user_id', $request->user()->id)],
            'time_type' => ['required', 'string', 'max:120'],
            'order_note' => ['nullable', 'string', 'min:5', 'max:200', 'regex:/^[\pL\pN .,-]+$/u'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'Vui lòng thêm thông tin người nhận.',
            'name.min' => 'Họ tên phải có độ dài từ 2-50 ký tự.',
            'name.max' => 'Họ tên phải có độ dài từ 2-50 ký tự.',
            'name.regex' => 'Họ tên chỉ được chứa chữ cái và khoảng trắng.',
            'phone.required' => 'Vui lòng thêm thông tin người nhận.',
            'phone.regex' => 'Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0.',
            'address.required' => 'Vui lòng thêm địa chỉ nhận hàng.',
            'address.exists' => 'Vui lòng thêm địa chỉ nhận hàng.',
            'time_type.required' => 'Vui lòng lựa chọn thời gian giao hàng.',
            'order_note.min' => 'Ghi chú có độ dài từ 5-200 ký tự.',
            'order_note.max' => 'Ghi chú không được vượt quá 200 ký tự.',
            'order_note.regex' => 'Mô tả chỉ chứa chữ cái, chữ số, dấu chấm, dấu phẩy, dấu gạch ngang và khoảng trắng.',
        ]);

        $distanceKm = (new \App\Adapters\Map\OpenStreetMapAdapter())->getDistanceInKm(
            'Đại học Thủy Lợi, Đống Đa, Hà Nội',
            $validated['address']
        );
        if ($distanceKm === null || $distanceKm > 15) {
            throw ValidationException::withMessages([
                'address' => $distanceKm === null
                    ? 'Không thể xác định khoảng cách giao hàng. Vui lòng thử lại sau.'
                    : 'Địa chỉ nhận hàng vượt quá phạm vi phục vụ',
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
                    $rawCode = strtoupper(trim($validated['voucher_code']));
                    if (!preg_match('/^[A-Z0-9]+$/', $rawCode)) {
                        throw ValidationException::withMessages(['voucher_code' => 'Mã voucher không tồn tại.']);
                    }
                    $voucher = Voucher::whereRaw('UPPER(code) = ?', [$rawCode])->lockForUpdate()->first();
                    if ($voucherError = $this->voucherError($voucher, $subtotal, $request->user())) {
                        throw ValidationException::withMessages(['voucher_code' => $voucherError]);
                    }
                    $processor = VoucherFactory::make($voucher);
                    $discount = (float) $processor->calculateDiscount($subtotal, $voucher);
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

        if ($isBuyNow) {
            session()->forget(['buy_now_item', 'is_buy_now', 'applied_voucher']);
        } else {
            session()->forget(['cart', 'applied_voucher']);
        }
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
        $isBuyNow = session()->get('is_buy_now', false);
        $cart = $isBuyNow ? session()->get('buy_now_item', []) : session()->get('cart', []);
        $subtotal = collect($cart)->sum(fn ($item) => (float) $item['price'] * (int) $item['quantity']);
        $code = strtoupper(trim((string) $request->input('code', '')));

        if ($code === '') {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã giảm giá!'
            ]);
        }

        if (!preg_match('/^[A-Z0-9]+$/', $code)) {
            return response()->json([
                'success' => false,
                'message' => 'Mã voucher không tồn tại.',
            ]);
        }

        $voucher = Voucher::whereRaw('UPPER(code) = ?', [$code])->first();
        $user = $request->user() ?: auth()->user();

        if ($voucherError = $this->voucherError($voucher, $subtotal, $user)) {
            return response()->json([
                'success' => false,
                'message' => $voucherError,
            ]);
        }

        try {
            $processor = VoucherFactory::make($voucher);
            $discountAmount = (float) $processor->calculateDiscount($subtotal, $voucher);
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

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi, vui lòng thử lại sau',
            ]);
        }
    }

    private function voucherError(?Voucher $voucher, float $subtotal, ?\App\Models\User $user = null): ?string
    {
        if (!$voucher) {
            return 'Mã voucher không tồn tại.';
        }

        if ($voucher->starts_at && Carbon::parse($voucher->starts_at)->isFuture()) {
            return 'Voucher chưa đến thời gian áp dụng';
        }

        if ($voucher->expires_at && Carbon::parse($voucher->expires_at)->isPast()) {
            return 'Voucher đã hết thời gian áp dụng';
        }

        if ($voucher->quantity <= 0) {
            return 'Voucher đã hết lượt sử dụng.';
        }

        // Kiểm tra điều kiện voucher sinh nhật
        if ($voucher->isBirthday()) {
            if (!$user || empty($user->birth_date) || !Voucher::isWithinBirthdayWindow($user->birth_date)) {
                return 'Bạn chưa đủ điều kiện sử dụng voucher này';
            }
        }

        $minOrderValue = max(100000, (float) ($voucher->min_order_value ?: 0));
        if ($subtotal <= 0 || $subtotal < $minOrderValue) {
            return 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng voucher';
        }

        return null;
    }

    public function updateAddress(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate(['address' => ['required', 'string', 'min:5', 'max:255']], [
            'address.required' => 'Vui lòng thêm địa chỉ nhận hàng.',
        ]);
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
                return response()->json(['success' => false, 'message' => 'Địa chỉ nhận hàng vượt quá phạm vi phục vụ'], 422);
            }

            if (!$user->addresses()->where('address', $destinationAddress)->exists() && $user->addresses()->count() >= 10) {
                return response()->json(['success' => false, 'message' => 'Bạn chỉ được lưu tối đa 10 địa chỉ.'], 422);
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