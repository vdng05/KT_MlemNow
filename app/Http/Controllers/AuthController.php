<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Hiển thị form đăng nhập
    public function showLogin()
    {
        return view('auth.login');
    }

    // Xử lý đăng nhập (bằng Email hoặc Số điện thoại)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // Kiểm tra xem người dùng nhập email hay sđt
        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$fieldType => $request->login, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->route('home');
        }

        return back()->withErrors(['login' => 'Tài khoản hoặc mật khẩu không chính xác.']);
    }

    // Hiển thị form đăng ký
    public function showRegister()
    {
        return view('auth.register');
    }

    // Xử lý đăng ký
    public function register(Request $request)
    {
        // 1. Lấy dữ liệu được gửi từ form đăng ký.
        $name = $request->input('name');
        $phone = $request->input('phone');
        $email = $request->input('email');
        $password = $request->input('password');
        $password_confirmation = $request->input('password_confirmation');

        // 2. Kiểm tra định dạng dữ liệu; lỗi validation được trả về form (AF4).
        $validationErrors = [];
        $errorMessage = '';
        if (!$this->checkName($name, $errorMessage)) {
            $validationErrors['name'] = $errorMessage;
        }

        $errorMessage = '';
        if (!$this->checkEmail($email, $errorMessage)) {
            $validationErrors['email'] = $errorMessage;
        }

        $errorMessage = '';
        if (!$this->checkPhone($phone, $errorMessage)) {
            $validationErrors['phone'] = $errorMessage;
        }

        $errorMessage = '';
        if (!$this->checkPassword($password, $errorMessage)) {
            $validationErrors['password'] = $errorMessage;
        }

        if (!empty($validationErrors)) {
            return back()->withErrors($validationErrors)->withInput();
        }

        // 7. Email và số điện thoại phải chưa được đăng ký (AF1/AF2).
        if (!$this->checkEmailExist($email) && !$this->checkPhoneExist($phone)) {
            // 8. Mật khẩu xác nhận phải khớp với mật khẩu đã nhập (AF3).
            $errorMessage = '';
            if (!$this->checkConfirmPassword($password, $password_confirmation, $errorMessage)) {
                return back()->withErrors([
                    'password_confirmation' => $errorMessage,
                ])->withInput();
            }

            // 9. Mã hóa mật khẩu và tạo bản ghi người dùng trong cơ sở dữ liệu.
            try {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make($password),
                ]);
            } catch (QueryException $exception) {
                report($exception);

                return back()->withErrors([
                    'register' => 'Lỗi hệ thống: không thể tạo tài khoản. Vui lòng thử lại.',
                ])->withInput();
            }

            // 10. Đăng nhập và thông báo nếu tạo thành công; nếu không, báo lỗi (EF1).
            if ($user != null) {
                Auth::login($user);
                return redirect()->route('home')->with('success', 'Đăng ký tài khoản thành công.');
            }

            return back()->withErrors([
                'register' => 'Lỗi hệ thống: không thể tạo tài khoản. Vui lòng thử lại.',
            ])->withInput();
        }

        // Phân biệt lỗi trùng email và lỗi trùng số điện thoại.
        if ($this->checkEmailExist($email)) {
            return back()->withErrors([
                'email' => 'Email này đã được đăng ký.',
            ])->withInput();
        }

        return back()->withErrors([
            'phone' => 'Số điện thoại này đã được đăng ký.',
        ])->withInput();
    }

    private function checkName(mixed $name, string &$errorMessage): bool
    {
        $errorMessage = '';

        if (!is_string($name) || trim($name) === '') {
            $errorMessage = 'Họ tên không được bỏ trống.';
            return false;
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 50) {
            $errorMessage = 'Họ tên phải có độ dài từ 2 đến 50 ký tự.';
            return false;
        } elseif (preg_match('/[0-9]/', $name) === 1) {
            $errorMessage = 'Họ tên không được chứa chữ số.';
            return false;
        } elseif (preg_match('/[^\p{L} ]/u', $name) !== 0) {
            $errorMessage = 'Họ tên không được chứa ký tự đặc biệt.';
            return false;
        }

        return true;
    }

    private function checkEmail(mixed $email, string &$errorMessage): bool
    {
        $errorMessage = '';

        if (!is_string($email) || trim($email) === '') {
            $errorMessage = 'Vui lòng nhập email.';
            return false;
        } elseif (strlen($email) > 255 || preg_match('/\s/', $email) === 1) {
            $errorMessage = 'Email không được chứa khoảng trắng và không vượt quá 255 ký tự.';
            return false;
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errorMessage = 'Email không đúng định dạng.';
            return false;
        } elseif (preg_match('/\.com$/i', $email) !== 1) {
            $errorMessage = 'Email phải có đuôi .com.';
            return false;
        }

        return true;
    }

    

    private function checkPhone(mixed $phone, string &$errorMessage): bool
    {
        $errorMessage = '';

        // (1) Kiểm tra số điện thoại không bị bỏ trống và được gửi dưới dạng chuỗi.
        if (!is_string($phone) || trim($phone) === '') {
            // (2) Báo lỗi E5 khi số điện thoại bị bỏ trống.
            $errorMessage = 'Số điện thoại không được bỏ trống.';
            return false;
        }
        // (3) Từ chối ký tự không phải chữ số.
        elseif (preg_match('/[^0-9]/', $phone) === 1) {
            // (4) Báo lỗi khi số điện thoại chứa ký tự không phải chữ số.
            $errorMessage = 'Số điện thoại chỉ được chứa chữ số.';
            return false;
        }
        // (5) Kiểm tra số điện thoại có đúng 10 chữ số.
        elseif (strlen($phone) !== 10) {
            // (6) Báo lỗi E6 khi độ dài không hợp lệ.
            $errorMessage = 'Số điện thoại phải gồm đúng 10 chữ số.';
            return false;
        }
        // (7) Kiểm tra chữ số đầu tiên là số 0.
        elseif (strpos($phone, '0') !== 0) {
            // (8) Báo lỗi E7 khi số điện thoại không bắt đầu bằng số 0.
            $errorMessage = 'Số điện thoại phải bắt đầu bằng số 0.';
            return false;
        }
        // (9) Các điều kiện đều hợp lệ.
        else {
            // (10) Chấp nhận số điện thoại.
            return true;
        }
    }

    private function checkPassword(mixed $password, string &$errorMessage): bool
    {
        $errorMessage = '';

        if (!is_string($password) || $password === '') {
            $errorMessage = 'Vui lòng nhập mật khẩu.';
            return false;
        } elseif (mb_strlen($password) < 8 || mb_strlen($password) > 20) {
            $errorMessage = 'Mật khẩu phải có độ dài từ 8 đến 20 ký tự.';
            return false;
        } elseif (preg_match('/[A-Z]/', $password) !== 1) {
            $errorMessage = 'Mật khẩu phải có ít nhất một chữ cái viết hoa.';
            return false;
        } elseif (preg_match('/[0-9]/', $password) !== 1) {
            $errorMessage = 'Mật khẩu phải có ít nhất một chữ số.';
            return false;
        } elseif (preg_match('/[^A-Za-z0-9\s]/', $password) !== 1) {
            $errorMessage = 'Mật khẩu phải có ít nhất một ký tự đặc biệt.';
            return false;
        }

        return true;
        return true;
    }
    private function checkConfirmPassword(mixed $password, mixed $confirmPassword, string &$errorMessage): bool
    {
        $errorMessage = '';

        // (1) Kiểm tra mật khẩu xác nhận đã được nhập.
        if (!is_string($confirmPassword) || $confirmPassword === '') {
            // (2) Trả lỗi E18 khi chưa nhập mật khẩu xác nhận.
            $errorMessage = 'Vui lòng nhập lại mật khẩu.';
            return false;
        }
        // (3) Kiểm tra mật khẩu xác nhận có khớp mật khẩu ban đầu.
        elseif ($confirmPassword !== $password) {
            // (4) Trả lỗi E18 khi hai mật khẩu không khớp.
            $errorMessage = 'Mật khẩu nhập lại không khớp với mật khẩu.';
            return false;
        }
        // (5) Hai mật khẩu hợp lệ và khớp nhau.
        else {
            // (6) Chấp nhận mật khẩu xác nhận.
            return true;
        }
    }

    

    private function checkEmailExist(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    private function checkPhoneExist(string $phone): bool
    {
        return User::where('phone', $phone)->exists();
    }

    // Xử lý đăng xuất
    public function logout(Request $request)
    {
        // 11. Kết thúc phiên đăng nhập, làm mới token CSRF và quay về trang chủ.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}