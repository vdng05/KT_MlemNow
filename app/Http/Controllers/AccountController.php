<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function profile()
    {
        return view('account.profile', ['user' => $this->authenticatedUser()]);
    }

    public function updateProfile(Request $request)
    {
        $user = $this->authenticatedUser(); // (1) Lấy tài khoản đang đăng nhập.
        $name = $request->input('name'); // (2) Lấy họ tên.
        $phone = $request->input('phone'); // (3) Lấy số điện thoại.
        $email = $request->input('email'); // (4) Lấy email.
        $dob = $request->input('dob', $request->input('birth_date')); // (5) Nhận dob hoặc birth_date từ form hiện tại.

        // (6) Kiểm tra thủ công bốn trường và trả tất cả lỗi validation về form.
        $errors = [];
        if (!$this->checkValidProfileData($name, $phone, $email, $dob, $errors)) {
            return back()->withErrors($errors)->withInput();
        }

        // (7) Email và số điện thoại phải chưa thuộc tài khoản người dùng khác.
        $duplicateErrors = [];
        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            $duplicateErrors['email'] = 'Email này đã được sử dụng bởi tài khoản khác.';
        }
        if (User::where('phone', $phone)->where('id', '!=', $user->id)->exists()) {
            $duplicateErrors['phone'] = 'Số điện thoại này đã được sử dụng bởi tài khoản khác.';
        }
        if (!empty($duplicateErrors)) {
            return back()->withErrors($duplicateErrors)->withInput();
        }

        // (8) Giữ kiểm tra avatar đang được hỗ trợ bởi controller.
        $validatedAvatar = [];
        if ($request->hasFile('avatar')) {
            $validatedAvatar = $request->validate([
                'avatar' => ['image', 'mimes:jpg,jpeg,png', 'max:5120', 'dimensions:max_width=1920,max_height=1080'],
            ]);
        }

        // (9) Chuẩn bị các trường được phép cập nhật.
        $updateData = [
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'birth_date' => $dob === '' ? null : $dob,
        ];
        $oldAvatarPath = $user->avatar_path;

        if ($request->hasFile('avatar')) {
            $avatarPath = $validatedAvatar['avatar']->store('avatars', 'public');
            if ($avatarPath === false) {
                return back()->withErrors([
                    'avatar' => 'Không thể lưu ảnh đại diện. Vui lòng thử lại.',
                ])->withInput();
            }
            $updateData['avatar_path'] = $avatarPath;
        }

        // (10) Lưu thay đổi; xử lý lỗi cơ sở dữ liệu tường minh.
        try {
            $updated = $user->update($updateData);
        } catch (QueryException $exception) {
            if (isset($updateData['avatar_path'])) {
                Storage::disk('public')->delete($updateData['avatar_path']);
            }
            report($exception);

            return back()->withErrors([
                'profile' => 'Lỗi hệ thống: không thể cập nhật thông tin. Vui lòng thử lại.',
            ])->withInput();
        }

        if (!$updated) {
            if (isset($updateData['avatar_path'])) {
                Storage::disk('public')->delete($updateData['avatar_path']);
            }

            return back()->withErrors([
                'profile' => 'Không thể cập nhật thông tin. Vui lòng thử lại.',
            ])->withInput();
        }

        if (isset($updateData['avatar_path']) && $oldAvatarPath) {
            Storage::disk('public')->delete($oldAvatarPath);
        }

        // (11) Báo cập nhật thành công.
        return back()->with('success', 'Cập nhật thông tin cá nhân thành công.');
    }

    private function checkValidProfileData(mixed $name, mixed $phone, mixed $email, mixed $dob, array &$errors): bool
    {
        $errorMessage = '';
        if (!$this->checkUpdateName($name, $errorMessage)) {
            $errors['name'] = $errorMessage;
        }

        $errorMessage = '';
        if (!$this->checkUpdatePhone($phone, $errorMessage)) {
            $errors['phone'] = $errorMessage;
        }

        $errorMessage = '';
        if (!$this->checkUpdateEmail($email, $errorMessage)) {
            $errors['email'] = $errorMessage;
        }

        $errorMessage = '';
        if (!$this->checkUpdateDob($dob, $errorMessage)) {
            $errors['dob'] = $errorMessage;
        }

        return empty($errors);
    }

    private function checkUpdateName(mixed $name, string &$errorMessage): bool
    {
        $errorMessage = '';

        if (!is_string($name) || trim($name) === '') {
            $errorMessage = 'Họ tên không được bỏ trống.';
            return false;
        } elseif (mb_strlen($name, 'UTF-8') < 2 || mb_strlen($name, 'UTF-8') > 50) {
            $errorMessage = 'Họ tên phải có độ dài từ 2 đến 50 ký tự.';
            return false;
        } elseif (preg_match('/[0-9]/', $name) === 1) {
            $errorMessage = 'Họ tên không được chứa chữ số.';
            return false;
        } elseif (preg_match('/[^\p{L} ]/u', $name) !== 0) {
            $errorMessage = 'Họ tên không được chứa ký tự đặc biệt.';
            return false;
        } else {
            return true;
        }
    }

    private function checkUpdatePhone(mixed $phone, string &$errorMessage): bool
    {
        $errorMessage = '';

        if (!is_string($phone) || trim($phone) === '') {
            $errorMessage = 'Số điện thoại không được bỏ trống.';
            return false;
        } elseif (preg_match('/[^0-9]/', $phone) === 1) {
            $errorMessage = 'Số điện thoại chỉ được chứa chữ số.';
            return false;
        } elseif (strlen($phone) !== 10) {
            $errorMessage = 'Số điện thoại phải gồm đúng 10 chữ số.';
            return false;
        } elseif (strpos($phone, '0') !== 0) {
            $errorMessage = 'Số điện thoại phải bắt đầu bằng số 0.';
            return false;
        } else {
            return true;
        }
    }

    private function checkUpdateEmail(mixed $email, string &$errorMessage): bool
    {
        $errorMessage = '';

        if (!is_string($email) || trim($email) === '') {
            $errorMessage = 'Email không được bỏ trống.';
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
        } else {
            return true;
        }
    }

    private function checkUpdateDob(mixed $dob, string &$errorMessage): bool
    {
        $errorMessage = '';

        if ($dob === null || $dob === '') {
            return true;
        } elseif (!is_string($dob)) {
            $errorMessage = 'Ngày sinh không đúng định dạng.';
            return false;
        } elseif (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $dob) !== 1) {
            $errorMessage = 'Ngày sinh phải có định dạng năm-tháng-ngày.';
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $dob);
        $dateErrors = \DateTimeImmutable::getLastErrors();
        if ($date === false
            || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || $date->format('Y-m-d') !== $dob) {
            $errorMessage = 'Ngày sinh phải là một ngày hợp lệ theo định dạng năm-tháng-ngày.';
            return false;
        } elseif ((int) $date->format('Y') > 2026) {
            $errorMessage = 'Năm sinh không được lớn hơn năm 2026.';
            return false;
        } elseif ($date->format('Y-m-d') > now()->toDateString()) {
            $errorMessage = 'Ngày sinh không được ở tương lai.';
            return false;
        } else {
            return true;
        }
    }

    public function addresses()
    {
        $addresses = $this->authenticatedUser()->addresses()->orderByDesc('is_default')->latest()->get();

        return view('account.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request)
    {
        $user = $this->authenticatedUser();
        if ($user->addresses()->count() >= 10) {
            return back()->withErrors(['address_limit' => 'Mỗi tài khoản chỉ được lưu tối đa 10 địa chỉ.'])->withInput();
        }

        $validated = $this->validateAddress($request, $user->id);
        $validated['user_id'] = $user->id;
        $validated['address'] = $this->formatAddress($validated);
        $this->ensureAddressIsUnique($user->id, $validated['address']);
        $validated['is_default'] = $request->boolean('is_default') || !$user->addresses()->exists();

        if ($validated['is_default']) {
            $user->addresses()->update(['is_default' => false]);
        }

        UserAddress::create($validated);

        return redirect()->route('account.addresses.index')->with('success', 'Đã lưu địa chỉ giao hàng.');
    }

    public function updateAddress(Request $request, UserAddress $address)
    {
        $user = $this->authenticatedUser();
        abort_unless($address->user_id === $user->id, 404);
        $validated = $this->validateAddress($request, $user->id, $address->id, !$address->ward);
        $validated['address'] = $this->formatAddress($validated);
        $this->ensureAddressIsUnique($user->id, $validated['address'], $address->id);
        $validated['is_default'] = $request->boolean('is_default');

        if ($validated['is_default']) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()->with('success', 'Đã cập nhật địa chỉ giao hàng.');
    }

    public function deleteAddress(Request $request, UserAddress $address)
    {
        $user = $this->authenticatedUser();
        abort_unless($address->user_id === $user->id, 404);
        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $user->addresses()->latest()->first()?->update(['is_default' => true]);
        }

        return back()->with('success', 'Đã xóa địa chỉ giao hàng.');
    }

    public function setDefaultAddress(Request $request, UserAddress $address)
    {
        $user = $this->authenticatedUser();
        abort_unless($address->user_id === $user->id, 404);
        $user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'Đã đặt địa chỉ mặc định.');
    }

    private function authenticatedUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    private function validateAddress(Request $request, int $userId, ?int $addressId = null, bool $allowMissingWard = false): array
    {
        return $request->validate([
            'recipient_name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\pL ]+$/u'],
            'phone' => ['required', 'regex:/^0[0-9]{9}$/'],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'ward' => [$allowMissingWard ? 'nullable' : 'required', 'string', 'max:100'],
            'detail' => ['required', 'string', 'min:5', 'max:100'],
            'address_type' => ['nullable', Rule::in(['home', 'work'])],
            'is_default' => ['nullable', 'boolean'],
            'address' => [Rule::unique('user_addresses', 'address')->where('user_id', $userId)->ignore($addressId)],
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
        $query = UserAddress::where('user_id', $userId)->where('address', $formattedAddress);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages(['detail' => 'Địa chỉ này đã được lưu trong tài khoản.']);
        }
    }
}
