@extends('layouts.app')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

@section('content')
<div class="container" style="max-width: 500px; margin-top: 50px; background: white; padding: 30px; border-radius: 15px;">
    <div class="auth-logo-wrapper" style="margin-bottom: 15px; text-align: center;">
        <img src="{{ asset('images/logo.png') }}" alt="MlemNow Logo" style="height: 80px; width: auto; object-fit: contain; display: inline-block;">
    </div>
    <h2 style="text-align: center; color: #ff5722; margin-bottom: 20px;">Đăng Nhập</h2>
    
    @if($errors->any())
        <div style="color: red; margin-bottom: 15px; text-align: center;">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('login') }}" method="POST" class="checkout-form">
        @csrf
        <div class="form-group">
            <label>Email hoặc Số điện thoại</label>
            <input type="text" name="login" required placeholder="Nhập email hoặc SĐT">
        </div>
        
        <div class="form-group">
            <label>Mật khẩu</label>
            <div style="position: relative;">
                <input type="password" name="password" required placeholder="Nhập mật khẩu" style="padding-right: 40px; width: 100%; box-sizing: border-box;">
                <i class="fas fa-eye toggle-password" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888;"></i>
            </div>
        </div>
        
        <button type="submit" class="confirm-order-btn">Đăng nhập</button>
    </form>
    
    <p style="text-align: center; margin-top: 15px;">
        Chưa có tài khoản? <a href="{{ route('register') }}" style="color: #ff5722; font-weight: bold; text-decoration: none;">Đăng ký ngay</a>
    </p>
</div>

<script>
    // JS Xử lý ẩn hiện mật khẩu
    document.querySelectorAll('.toggle-password').forEach(item => {
        item.addEventListener('click', function () {
            const input = this.closest('div').querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    });
</script>
@endsection