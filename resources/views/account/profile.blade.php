@extends('layouts.app')

@section('content')
<style>
    .account-page { --account-orange: #ed642d; --account-ink: #34251f; max-width: 820px; margin: 42px auto 64px; padding: 0 20px; color: var(--account-ink); }
    .account-page h1 { margin: 0 0 8px; color: var(--account-orange); font-size: 30px; }
    .account-page .account-subtitle { margin: 0 0 26px; color: #76655c; }
    .account-panel { background: #fff; border: 1px solid #f2d5c7; border-top: 4px solid var(--account-orange); border-radius: 10px; padding: 26px; box-shadow: 0 12px 32px rgba(100, 54, 31, .08); }
    .account-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .account-field { display: grid; gap: 7px; color: #604b40; font-size: 14px; font-weight: 700; }
    .account-field input { width: 100%; min-height: 44px; box-sizing: border-box; padding: 10px 12px; color: var(--account-ink); background: #fffdfb; border: 1px solid #e6d8d1; border-radius: 6px; font: inherit; font-weight: 400; }
    .account-field input:focus { outline: 2px solid #f5a17c; border-color: var(--account-orange); }
    .account-submit { margin-top: 22px; padding: 12px 20px; color: white; background: var(--account-orange); border: 0; border-radius: 6px; font: inherit; font-weight: 700; cursor: pointer; }
    .account-submit:hover { background: #cf4f20; }
    .account-alert { margin-bottom: 18px; padding: 12px 14px; color: #9e321d; background: #fff1e9; border: 1px solid #f3c5b0; border-radius: 6px; }
    @media (max-width: 620px) { .account-page { margin-top: 26px; } .account-panel { padding: 18px; } .account-form-grid { grid-template-columns: 1fr; } }
</style>
<section class="account-page">
    <h1>Thông tin tài khoản</h1>
    <p class="account-subtitle">Cập nhật thông tin liên hệ của bạn.</p>
    @if($errors->any())
        <div class="account-alert" role="alert">{{ $errors->first() }}</div>
    @endif
    @if(session('success'))
        <div class="account-alert" role="status">{{ session('success') }}</div>
    @endif
    <div class="account-panel">
        <form action="{{ route('account.profile.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="account-form-grid">
                <label class="account-field">Họ và tên
                    <input name="name" value="{{ old('name', $user->name) }}" required minlength="2" maxlength="50" pattern="[A-Za-zÀ-ỹ ]+" autocomplete="name">
                </label>
                <label class="account-field">Email
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required pattern="[^@\s]+@[^@\s]+\.com" autocomplete="email">
                </label>
                <label class="account-field">Số điện thoại
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required pattern="0[0-9]{9}" maxlength="10" autocomplete="tel">
                </label>
                <label class="account-field">Ngày sinh
                    <input type="date" name="birth_date" value="{{ old('birth_date', $user->birth_date) }}" max="{{ now()->toDateString() }}">
                </label>
            </div>
            <button class="account-submit" type="submit">Lưu thông tin</button>
        </form>
    </div>
</section>
@endsection