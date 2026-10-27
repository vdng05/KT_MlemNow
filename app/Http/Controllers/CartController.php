<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Food;
use App\Decorators\BaseFood;
use App\Decorators\SizeDecorator;
use App\Decorators\ToppingDecorator;

class CartController extends Controller
{
    public function index()
    {
        // Khi người dùng vào trang giỏ hàng, hủy trạng thái Mua ngay
        session()->forget(['buy_now_item', 'is_buy_now']);

        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $food = Food::findOrFail($request->food_id);

        $foodItem = new BaseFood($food);

        // Lấy tên Size để lưu riêng
        $sizeNameForCart = '';
        if ($request->has('size')) {
            list($sizeName, $sizePrice) = explode('|', $request->size);
            if ($sizePrice > 0) {
                $foodItem = new SizeDecorator($foodItem, $sizeName, (float)$sizePrice);
            }
            $sizeNameForCart = $sizeName;
        }

        // Lấy danh sách Topping để lưu riêng
        $toppingsForCart = [];
        if ($request->has('toppings')) {
            foreach ($request->toppings as $topping) {
                list($toppingName, $toppingPrice) = explode('|', $topping);
                $foodItem = new ToppingDecorator($foodItem, $toppingName, (float)$toppingPrice);
                $toppingsForCart[] = $toppingName;
            }
        }

        $finalName = $foodItem->getDescription();
        $finalPrice = $foodItem->getPrice();
        if ($finalPrice <= 0) {
            $finalPrice = $food->price; // Fallback về giá gốc nếu Decorator lỗi
        }
        $note = $request->input('note', '');

        // Tạo ID duy nhất cho món
        $cartId = md5($finalName . $note); 

        $itemData = [
            'id' => $food->id,
            'name' => $finalName, 
            'base_name' => $food->name, // Tên gốc: Mì cay thập cẩm
            'size' => $sizeNameForCart, // Phân loại: Size L
            'toppings' => implode(' • ', $toppingsForCart), // Topping: Thêm phô mai • Xúc xích
            'price' => (float)$finalPrice, 
            'image' => $food->image,
            'quantity' => (int) $request->quantity,
            'note' => $note,
            'restaurant' => $food->restaurant->name ?? 'Quán ăn đối tác'
        ];

        // NẾU LÀ "MUA NGAY": Chỉ mua món này, KHÔNG cộng dồn vào giỏ hàng chung
        if ($request->input('action') === 'buy_now') {
            session()->put('buy_now_item', [$cartId => $itemData]);
            session()->put('is_buy_now', true);
            session()->forget('applied_voucher');

            if (auth()->check()) {
                return redirect()->route('checkout.index'); 
            } else {
                return redirect()->route('login')->withErrors(['login' => 'Vui lòng đăng nhập để tiếp tục đặt hàng.']);
            }
        }

        // NẾU LÀ "THÊM VÀO GIỎ HÀNG": Cộng dồn vào giỏ hàng chung
        $cart = session()->get('cart', []);
        if(isset($cart[$cartId])) {
            $cart[$cartId]['quantity'] += (int) $request->quantity;
        } else {
            $cart[$cartId] = $itemData;
        }
        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Đã thêm vào giỏ thành công!');
    }

    public function remove(Request $request)
    {
        $cart = session()->get('cart');
        if(isset($cart[$request->cart_id])) {
            unset($cart[$request->cart_id]);
            session()->put('cart', $cart);
        }
        return redirect()->route('cart.index')->with('success', 'Đã xóa món khỏi giỏ.');
    }

    public function update(Request $request)
    {
        $cart = session()->get('cart');
        if(isset($cart[$request->cart_id])) {
            $cart[$request->cart_id]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
        }
        return redirect()->route('cart.index');
    }
}