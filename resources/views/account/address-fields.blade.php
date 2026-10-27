@php
	$address = $address ?? null;
	$segments = $address ? array_values(array_filter(array_map('trim', explode(',', $address->address ?? '')))) : [];
	$legacyProvince = count($segments) >= 1 ? $segments[count($segments) - 1] : '';
	$legacyDistrict = count($segments) >= 3 ? $segments[count($segments) - 2] : '';
	$legacyWard = count($segments) >= 5 ? $segments[count($segments) - 3] : '';
	$legacyDetailCount = count($segments) >= 5 ? count($segments) - 3 : (count($segments) === 4 ? 2 : count($segments));
	$legacyDetail = implode(', ', array_slice($segments, 0, $legacyDetailCount));
@endphp
<p><label>Tên người nhận <input name="recipient_name" value="{{ old('recipient_name', $address->recipient_name ?? auth()->user()->name) }}" required minlength="2" maxlength="50" pattern="[A-Za-zÀ-ỹ ]+"></label></p>
<p><label>Số điện thoại <input type="tel" name="phone" value="{{ old('phone', $address->phone ?? auth()->user()->phone) }}" required pattern="0[0-9]{9}" maxlength="10"></label></p>
<p><label>Tỉnh/Thành phố <input name="province" value="{{ old('province', $address->province ?? $legacyProvince) }}" required maxlength="100"></label></p>
<p><label>Quận/Huyện <input name="district" value="{{ old('district', $address->district ?? $legacyDistrict) }}" required maxlength="100"></label></p>
<p><label>Phường/Xã <input name="ward" value="{{ old('ward', $address->ward ?? $legacyWard) }}" {{ !$address || $address->ward ? 'required' : '' }} maxlength="100" placeholder="{{ $address && !$address->ward ? 'Chưa có trong địa chỉ cũ; có thể chọn trên bản đồ' : '' }}"></label></p>
<p><label>Địa chỉ cụ thể <input name="detail" value="{{ old('detail', $address->detail ?? $legacyDetail) }}" required minlength="5" maxlength="100"></label></p>
<button class="address-map-button" type="button" onclick="openAddressMap(this.form)">Chọn vị trí trên bản đồ</button>
<p><label>Loại địa chỉ <select name="address_type"><option value="home" @selected(old('address_type', $address->address_type ?? 'home') === 'home')>Nhà riêng</option><option value="work" @selected(old('address_type', $address->address_type ?? '') === 'work')>Công ty</option></select></label></p>
<p><label><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address->is_default ?? false))> Đặt làm địa chỉ mặc định</label></p>