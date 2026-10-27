<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Models\Food;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AccountController;


//Trang chủ
Route::get('/', [HomeController::class, 'index'])->name('home');

//Lấy id món ăn
Route::get('/food/{id}', function ($id) {
    // Lấy thông tin món ăn kèm theo quán, size và topping
    $food = Food::with(['restaurant', 'sizes', 'toppings', 'reviews.user'])->findOrFail($id);
    return view('food.detail', compact('food'));
})->name('food.detail');

//Đăng nhập, đăng ký, đăng xuất
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Tìm kiếm món ăn và quán ăn
Route::get('/search', [SearchController::class, 'search'])->name('search');

// Danh sách quán ăn
Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');

// Giỏ hàng
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');

// Tiến hành đặt hàng
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success')->middleware('auth');
Route::post('/checkout/check-voucher', [CheckoutController::class, 'checkVoucher'])->name('checkout.check_voucher')->middleware('auth');
// Đưa các route checkout vào nhóm bắt buộc phải đăng nhập
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::get('/account/addresses', [AccountController::class, 'addresses'])->name('account.addresses.index');
    Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('account.addresses.store');
    Route::put('/account/addresses/{address}', [AccountController::class, 'updateAddress'])->name('account.addresses.update');
    Route::delete('/account/addresses/{address}', [AccountController::class, 'deleteAddress'])->name('account.addresses.delete');
    Route::post('/account/addresses/{address}/default', [AccountController::class, 'setDefaultAddress'])->name('account.addresses.default');
});

// Cập nhật địa chỉ giao hàng của người dùng
Route::post('/user/update-address', [App\Http\Controllers\CheckoutController::class, 'updateAddress'])
    ->name('user.update_address')
    ->middleware('auth');

// Lịch sử đơn hàng và đặt lại
Route::get('/orders', [OrderController::class, 'history'])->name('orders.history')->middleware('auth');
Route::post('/orders/{id}/reorder', [OrderController::class, 'reorder'])->name('orders.reorder')->middleware('auth');
Route::post('/order/{id}/confirm', [OrderController::class, 'confirmReceived'])->name('order.confirm')->middleware('auth');
Route::post('/orders/{id}/export-pdf', [OrderController::class, 'exportPdf'])->name('order.export-pdf')->middleware('auth');
Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel')->middleware('auth');
Route::get('/orders/{id}/review', [OrderController::class, 'reviewForm'])->name('orders.review')->middleware('auth');
Route::post('/orders/{id}/review', [OrderController::class, 'storeReview'])->name('orders.review.store')->middleware('auth');
Route::get('/staff/orders', [OrderController::class, 'staffOrders'])->name('staff.orders')->middleware('auth');
Route::post('/staff/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('staff.orders.status')->middleware('auth');