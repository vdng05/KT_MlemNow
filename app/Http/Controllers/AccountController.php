<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function profile()
    {
        return view('account.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
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
                'required',
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
                'email', 'max:255', 'regex:/^[^\s@]+@[^\s@]+\.com$/i', Rule::unique('users', 'email')->ignore($user->id),
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
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120', 'dimensions:max_width=1920,max_height=1080'],
            'remove_avatar' => ['nullable', 'boolean'],
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
            'birth_date.date' => 'Ngày sinh không hợp lệ.',
            'birth_date.before_or_equal' => 'Ngày sinh không được ở trong tương lai.',
            'avatar.image' => 'Ảnh đại diện phải là tệp hình ảnh.',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng JPG, JPEG hoặc PNG.',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 5 MB.',
            'avatar.dimensions' => 'Ảnh đại diện không được vượt quá 1920×1080 pixel.',
        ]);

        $previousAvatar = $user->avatar_path;
        $newAvatarPath = null;

        try {
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $avatarDirectory = public_path('images/avatars');
                File::ensureDirectoryExists($avatarDirectory);

                $avatarFilename = Str::uuid().'.'.$avatar->extension();
                $avatar->move($avatarDirectory, $avatarFilename);
                $newAvatarPath = 'images/avatars/'.$avatarFilename;
                $validated['avatar_path'] = $newAvatarPath;
            } elseif ($request->boolean('remove_avatar')) {
                $validated['avatar_path'] = null;
            }

            unset($validated['avatar'], $validated['remove_avatar']);
            if (!$user->fill($validated)->save()) {
                throw new \RuntimeException('Profile update was not saved.');
            }
        } catch (\Throwable $exception) {
            if ($newAvatarPath) {
                $this->deleteAvatar($newAvatarPath);
            }
            report($exception);
            $message = $exception instanceof \Illuminate\Database\ConnectionException
                ? 'Không thể kết nối đến máy chủ.'
                : 'Cập nhật thông tin thất bại.';

            return back()->withErrors(['profile' => $message])->withInput($request->except('avatar'));
        }

        if ($previousAvatar && ($newAvatarPath || $request->boolean('remove_avatar'))) {
            $this->deleteAvatar($previousAvatar);
        }

        return back()->with('success', 'Cập nhật thông tin thành công');
    }

    private function deleteAvatar(string $avatarPath): void
    {
        if (Str::startsWith($avatarPath, 'images/avatars/')) {
            File::delete(public_path('images/avatars/'.basename($avatarPath)));
        } elseif (Str::startsWith($avatarPath, 'avatars/')) {
            Storage::disk('public')->delete($avatarPath);
        }
    }

    public function addresses()
    {
        $addresses = auth()->user()->addresses()->orderByDesc('is_default')->latest()->get();

        return view('account.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request)
    {
        $user = $request->user(); //1
        if ($user->addresses()->count() >= 10) { //2
            return back()->withErrors(['address_limit' => 'Bạn chỉ được lưu tối đa 10 địa chỉ.'])->withInput(); //3
        }

        $validated = $this->validateAddress($request, $user->id); //4
        $validated['user_id'] = $user->id; //5
        $validated['address'] = $this->formatAddress($validated);
        $this->ensureAddressIsUnique($user->id, $validated['address']);
        $validated['is_default'] = $request->boolean('is_default') || !$user->addresses()->exists();

        try { //6
            if ($validated['is_default']) { //7
                $user->addresses()->update(['is_default' => false]); //8
            }

            UserAddress::create($validated); //9
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['address' => 'Thao tác không thể thực hiện. Vui lòng thử lại sau'])->withInput(); //10
        }

        return redirect()->route('account.addresses.index')->with('success', 'Địa chỉ mới đã được thêm thành công.'); //11
    }

    public function updateAddress(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        $validated = $this->validateAddress($request, $request->user()->id, $address->id);
        $validated['address'] = $this->formatAddress($validated);
        $this->ensureAddressIsUnique($request->user()->id, $validated['address'], $address->id);
        $validated['is_default'] = $request->boolean('is_default');

        try {
            if ($validated['is_default']) {
                $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update($validated);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['address' => 'Thao tác không thể thực hiện. Vui lòng thử lại sau'])->withInput();
        }

        return back()->with('success', 'Đã cập nhật địa chỉ giao hàng.');
    }

    public function deleteAddress(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        try {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $request->user()->addresses()->latest()->first()?->update(['is_default' => true]);
            }
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['address' => 'Thao tác không thể thực hiện. Vui lòng thử lại sau'])->withInput();
        }

        return back()->with('success', 'Xóa thành công');
    }

    public function setDefaultAddress(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        try {
            $request->user()->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['address' => 'Thao tác không thể thực hiện. Vui lòng thử lại sau'])->withInput();
        }

        return back()->with('success', 'Đã đặt địa chỉ mặc định.');
    }

    private function validateAddress(Request $request, int $userId, ?int $addressId = null): array
    {
        return $request->validate([
            'recipient_name' => [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    if (is_string($value) && trim($value) === '' && $value !== '') {
                        $fail('Tên người nhận không được chỉ gồm khoảng trắng.');
                    }
                },
                function ($attribute, $value, $fail) {
                    if (is_string($value) && preg_match('/[\pN]/u', $value)) {
                        $fail('Tên người nhận không được chứa chữ số.');
                    }
                },
                'regex:/^[\pL ]+$/u', 'min:2', 'max:50',
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
            ],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'ward' => ['required', 'string', 'max:100'],
            'detail' => ['required', 'string', 'min:5', 'max:100'],
            'address_type' => ['nullable', Rule::in(['home', 'work'])],
            'is_default' => ['nullable', 'boolean'],
            'address' => [Rule::unique('user_addresses', 'address')->where('user_id', $userId)->ignore($addressId)],
        ], [
            'recipient_name.required' => 'Vui lòng nhập tên người nhận.',
            'recipient_name.min' => 'Tên người nhận phải có độ dài từ 2-50 ký tự.',
            'recipient_name.max' => 'Tên người nhận phải có độ dài từ 2-50 ký tự.',
            'recipient_name.regex' => 'Tên người nhận không được chứa ký tự đặc biệt.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'detail.required' => 'Vui lòng nhập địa chỉ cụ thể.',
            'detail.min' => 'Địa chỉ cụ thể phải có độ dài từ 5-100 ký tự.',
            'detail.max' => 'Địa chỉ cụ thể phải có độ dài từ 5-100 ký tự.',
        ]);
    }

    private function formatAddress(array $address): string
    {
        return implode(', ', array_filter([
            $address['detail'], $address['ward'], $address['district'], $address['province'],
        ], fn ($part) => filled($part)));
    }

    private function ensureAddressIsUnique(int $userId, string $formattedAddress, ?int $ignoreId = null): void
    {
        $query = UserAddress::where('user_id', $userId)->where('address', $formattedAddress); //1
        if ($ignoreId) { //2
            $query->where('id', '!=', $ignoreId); //3
        }
        if ($query->exists()) { //4
            throw ValidationException::withMessages(['detail' => 'Địa chỉ này đã được lưu trong tài khoản.']); //5
        }
        return; //6
    }
}