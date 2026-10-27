<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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
        $request->validate([
            'name' => [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    if (is_string($value) && trim($value) === '' && $value !== '') {
                        $fail('Họ tên không được chỉ gồm khoảng trắng.');
                    }
                },
                function ($attribute, $value, $fail) {
                    if (is_string($value) && preg_match('/[\pN]/u', $value)) {
                        $fail('Họ tên không được chứa chữ số.');
                    }
                },
                'regex:/^[\pL ]+$/u', 'min:2', 'max:50',
            ],
            'email' => [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    if (!is_string($value) || $value === '') {
                        return;
                    }
                    if (preg_match('/\s/u', $value)) {
                        $fail('Email không được chứa khoảng trắng.');
                    } elseif (!str_contains($value, '@')) {
                        $fail('Email phải có cấu trúc <tên>@<tên miền>.');
                    } elseif (str_starts_with($value, '@')) {
                        $fail('Email phải có tên trước ký tự @.');
                    } elseif (str_ends_with($value, '@')) {
                        $fail('Email phải có tên miền sau ký tự @.');
                    }
                },
                'email', 'max:255', 'regex:/^[^\s@]+@[^\s@]+\.com$/i', 'unique:users,email',
            ],
            'phone' => [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    if (!is_string($value) || $value === '') {
                        return;
                    }
                    if (!ctype_digit($value)) {
                        $fail('Số điện thoại chỉ được chứa chữ số.');
                    } elseif (strlen($value) !== 10) {
                        $fail('Số điện thoại phải có độ dài bằng 10 chữ số.');
                    } elseif (!str_starts_with($value, '0')) {
                        $fail('Số điện thoại phải bắt đầu bằng số 0.');
                    }
                },
                'unique:users,phone',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:20',
                function ($attribute, $value, $fail) {
                    if (is_string($value) && !preg_match('/[A-Z]/', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất 1 chữ in hoa.');
                    }
                },
                function ($attribute, $value, $fail) {
                    if (is_string($value) && !preg_match('/[0-9]/', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất 1 chữ số.');
                    }
                },
                function ($attribute, $value, $fail) {
                    if (is_string($value) && !preg_match('/[^A-Za-z0-9\s]/', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt.');
                    }
                },
                'confirmed',
            ],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'name.min' => 'Họ tên phải có độ dài từ 2-50 ký tự.',
            'name.max' => 'Họ tên phải có độ dài từ 2-50 ký tự.',
            'name.regex' => 'Họ tên không được chứa ký tự đặc biệt.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email phải có cấu trúc <tên>@<tên miền>.',
            'email.regex' => 'Tên miền email phải có đuôi .com.',
            'email.unique' => 'Email đã được sử dụng.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.unique' => 'Số điện thoại đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có độ dài từ 8-20 ký tự.',
            'password.max' => 'Mật khẩu phải có độ dài từ 8-20 ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp với mật khẩu.',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['register' => 'Không thể hoàn tất đăng ký. Vui lòng thử lại sau.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        Auth::login($user);
        return redirect()->route('home')->with('success', 'Đăng ký thành công.');
    }

    // Xử lý đăng xuất
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}