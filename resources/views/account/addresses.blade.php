@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    .address-page { --address-orange: #ed642d; --address-ink: #34251f; max-width: 900px; margin: 42px auto 64px; padding: 0 20px; color: var(--address-ink); }
    .address-page h1 { margin: 0 0 7px; color: var(--address-orange); font-size: 30px; }
    .address-page .address-subtitle { margin: 0 0 22px; color: #76655c; }
    .address-shell { padding: 20px; background: #fff; border: 1px solid #f0cbb8; border-radius: 12px; box-shadow: 0 12px 30px rgba(100, 54, 31, .08); }
    .address-list { display: grid; gap: 12px; }
    .address-card { display: grid; grid-template-columns: 32px minmax(0, 1fr) auto; gap: 10px; align-items: center; padding: 17px 16px; border: 1px solid #d9d5d1; border-radius: 10px; background: #fff; transition: border-color .18s, background .18s, box-shadow .18s; }
    .address-card.is-default { border-color: #f0a77f; background: #fff8f3; box-shadow: inset 0 0 0 1px #f8e4d8; }
    .address-pin { display: grid; width: 30px; height: 30px; place-items: center; color: var(--address-orange); font-size: 28px; line-height: 1; }
    .address-recipient { font-size: 17px; font-weight: 750; }
    .address-phone { margin-left: 5px; color: #695950; font-weight: 600; }
    .address-text { margin: 5px 0 0; color: #594b44; line-height: 1.5; }
    .address-tag { display: inline-block; margin-top: 8px; padding: 3px 8px; color: #b44a20; background: #ffeadf; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .address-actions { display: flex; gap: 7px; align-items: center; }
    .address-icon-button { display: inline-grid; width: 36px; height: 36px; place-items: center; color: #4f413a; background: transparent; border: 1px solid transparent; border-radius: 7px; font-size: 19px; cursor: pointer; }
    .address-icon-button:hover, .address-icon-button:focus-visible { color: #bd4b1d; background: #ffefe7; border-color: #f4c3aa; outline: none; }
    .address-icon-button.delete:hover { color: #a72e22; background: #fff0ed; border-color: #f0b8af; }
    .address-add-button { display: flex; width: 100%; min-height: 52px; justify-content: center; align-items: center; gap: 8px; margin-top: 14px; color: #a94319; background: #fff; border: 1px solid #ed9b72; border-radius: 9px; font: inherit; font-size: 17px; font-weight: 750; cursor: pointer; }
    .address-add-button:hover { background: #fff3ec; }
    .address-count { margin: 0 0 14px; color: #78665c; font-size: 13px; text-align: right; }
    .address-dialog { --address-orange: #ed642d; --address-ink: #34251f; width: min(620px, calc(100% - 32px)); max-height: min(88vh, 760px); overflow: auto; padding: 24px; color: var(--address-ink); background: #fffdfb; border: 1px solid #efc8b4; border-radius: 12px; box-shadow: 0 24px 80px rgba(40, 22, 14, .25); }
    .address-dialog[open] { position: fixed; inset: 0; margin: auto; }
    .address-dialog::backdrop { background: rgba(35, 24, 19, .56); }
    .address-dialog h2 { margin: 0 0 18px; color: var(--address-orange); }
    .address-page label, .address-dialog label { display: block; margin: 12px 0; color: #604b40; font-size: 14px; font-weight: 700; }
    .address-page input:not([type=checkbox]), .address-page select, .address-dialog input:not([type=checkbox]), .address-dialog select { display: block; width: 100%; min-height: 42px; box-sizing: border-box; margin-top: 6px; padding: 9px 11px; color: var(--address-ink); background: #fff; border: 1px solid #e4d6ce; border-radius: 6px; font: inherit; font-weight: 400; }
    .address-page input:focus, .address-page select:focus, .address-dialog input:focus, .address-dialog select:focus { outline: 2px solid #f5a17c; border-color: var(--address-orange); }
    .address-page input[type=checkbox], .address-dialog input[type=checkbox] { display: inline-block; width: auto; min-height: 0; margin: 0 6px 0 0; accent-color: var(--address-orange); }
    .address-dialog form p { margin: 0 0 10px; }
    .address-dialog form p:has(input[type=checkbox]) label { display: flex; align-items: center; }
    .address-map-button { margin: 2px 0 12px; padding: 9px 13px; color: #b84417; background: #fff2e9; border: 1px solid #edaa88; border-radius: 6px; font: inherit; font-weight: 700; cursor: pointer; }
    .address-map-button:hover { background: #ffe6d8; }
    .address-map-dialog { --address-orange: #ed642d; --address-ink: #34251f; width: min(720px, calc(100% - 32px)); }
    .address-map-search { display: flex; gap: 8px; margin: 0 0 12px; }
    .address-map-search input { flex: 1; min-width: 0; }
    .address-map-search button, .address-map-actions button { padding: 10px 14px; color: white; background: #ed642d; border: 0; border-radius: 6px; font: inherit; font-weight: 700; cursor: pointer; }
    .address-map-canvas { width: 100%; height: min(52vh, 420px); min-height: 260px; border: 1px solid #e4d6ce; border-radius: 8px; z-index: 0; }
    .address-map-status { min-height: 20px; margin: 10px 0 0; color: #76594a; font-size: 13px; }
    .address-map-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 14px; }
    .address-map-actions button:first-child { color: #594b44; background: #fff; border: 1px solid #dfcfc6; }
    .address-dialog-actions { display: flex; justify-content: flex-end; gap: 9px; margin-top: 18px; }
    .address-dialog-actions button { padding: 10px 16px; border: 1px solid #dfcfc6; border-radius: 6px; background: white; font: inherit; cursor: pointer; }
    .address-dialog-actions button[type=submit] { color: white; background: var(--address-orange); border-color: var(--address-orange); font-weight: 700; }
    .address-error { margin-bottom: 14px; padding: 11px 13px; color: #9e321d; background: #fff1e9; border: 1px solid #f3c5b0; border-radius: 6px; }
    @media (max-width: 560px) { .address-page { margin-top: 26px; } .address-shell { padding: 12px; } .address-card { grid-template-columns: 25px minmax(0, 1fr); padding: 14px 11px; } .address-actions { grid-column: 2; justify-content: flex-end; } .address-recipient { font-size: 15px; } .address-phone { display: block; margin: 3px 0 0; } }
</style>
<section class="address-page">
    <h1>Quản lý địa chỉ giao hàng</h1>
    <p class="address-subtitle">Lưu địa chỉ nhận món và chọn địa chỉ mặc định của bạn.</p>
    @if($errors->any())<div class="address-error" role="alert">{{ $errors->first() }}</div>@endif
    @if(session('success'))<div class="address-error" role="status">{{ session('success') }}</div>@endif

    <div class="address-shell">
        <p class="address-count">{{ $addresses->count() }} / 10 địa chỉ</p>
        <div class="address-list">
            @forelse($addresses as $address)
                <article class="address-card {{ $address->is_default ? 'is-default' : '' }}">
                    <span class="address-pin" aria-hidden="true">⌂</span>
                    <div>
                        <div class="address-recipient">{{ $address->recipient_name }} <span class="address-phone">{{ $address->phone }}</span></div>
                        <p class="address-text">{{ $address->address }}</p>
                        @if($address->is_default)<span class="address-tag">Mặc định</span>@endif
                        @if($address->address_type)<span class="address-tag">{{ $address->address_type === 'work' ? 'Công ty' : 'Nhà riêng' }}</span>@endif
                    </div>
                    <div class="address-actions">
                        <button class="address-icon-button" type="button" title="Sửa địa chỉ" aria-label="Sửa địa chỉ" data-dialog-id="edit-address-{{ $address->id }}" data-address="{{ $address->address }}" onclick="openEditAddress(this.dataset.dialogId, this.dataset.address)">✎</button>
                        <form action="{{ route('account.addresses.delete', $address) }}" method="POST" onsubmit="return confirm('Bạn muốn xóa địa chỉ này?')">
                            @csrf
                            @method('DELETE')
                            <button class="address-icon-button delete" type="submit" title="Xóa địa chỉ" aria-label="Xóa địa chỉ">⌫</button>
                        </form>
                    </div>
                </article>

                <dialog class="address-dialog" id="edit-address-{{ $address->id }}">
                    <h2>Sửa địa chỉ giao hàng</h2>
                    <p class="address-prefill-status" role="status"></p>
                    <form action="{{ route('account.addresses.update', $address) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('account.address-fields', ['address' => $address])
                        <div class="address-dialog-actions">
                            <button type="button" onclick="this.closest('dialog').close()">Đóng</button>
                            <button type="submit">Lưu thay đổi</button>
                        </div>
                    </form>
                </dialog>
            @empty
                <p>Bạn chưa lưu địa chỉ giao hàng nào.</p>
            @endforelse
        </div>
        @if($addresses->count() < 10)
            <button class="address-add-button" type="button" onclick="document.getElementById('new-address-dialog').showModal()"><span aria-hidden="true">＋</span> Thêm địa chỉ mới</button>
        @endif
    </div>
</section>

<dialog class="address-dialog" id="new-address-dialog">
    <h2>Thêm địa chỉ giao hàng</h2>
    <form action="{{ route('account.addresses.store') }}" method="POST">
        @csrf
        @include('account.address-fields')
        <div class="address-dialog-actions">
            <button type="button" onclick="this.closest('dialog').close()">Hủy</button>
            <button type="submit">Lưu địa chỉ</button>
        </div>
    </form>
</dialog>

<dialog class="address-dialog address-map-dialog" id="address-map-dialog">
    <h2>Chọn vị trí giao hàng</h2>
    <div class="address-map-search">
        <input id="address-map-query" type="search" placeholder="Tìm địa chỉ, tòa nhà hoặc đường phố">
        <button type="button" onclick="searchAddressOnMap()">Tìm kiếm</button>
    </div>
    <div id="address-map-canvas" class="address-map-canvas"></div>
    <p id="address-map-status" class="address-map-status" role="status">Tìm kiếm hoặc nhấn lên bản đồ để chọn vị trí.</p>
    <div class="address-map-actions">
        <button type="button" onclick="document.getElementById('address-map-dialog').close()">Đóng</button>
        <button type="button" onclick="applyAddressMapSelection()">Dùng vị trí này</button>
    </div>
</dialog>

@push('scripts')
<script>
    let addressMap;
    let addressMapMarker;
    let addressMapPoint;
    let activeAddressForm;
    let resolvedMapAddress;

    function mappedAddressFields(parts, result) {
        return {
            detail: [parts.house_number, parts.road].filter(Boolean).join(' ') || parts.building || parts.amenity || result.name || '',
            ward: parts.ward || parts.suburb || parts.quarter || parts.neighbourhood || parts.village || '',
            district: parts.city_district || parts.district || parts.county || '',
            province: parts.city || parts.state || parts.province || parts.region || '',
        };
    }

    async function openEditAddress(dialogId, address) {
        const dialog = document.getElementById(dialogId);
        const form = dialog.querySelector('form');
        const status = dialog.querySelector('.address-prefill-status');
        dialog.showModal();

        const missingFields = ['ward', 'district', 'province'].filter(name => !form.elements[name].value.trim());
        if (!missingFields.length) return;

        status.textContent = 'Đang hoàn thiện các thành phần địa chỉ cũ...';
        try {
            const params = new URLSearchParams({ q: address, format: 'jsonv2', addressdetails: '1', limit: '1', countrycodes: 'vn' });
            const response = await fetch(`https://nominatim.openstreetmap.org/search?${params}`);
            if (!response.ok) throw new Error('Address lookup failed');
            const results = await response.json();
            const result = results[0];
            if (!result?.address) throw new Error('Address not found');

            const fields = mappedAddressFields(result.address, result);
            missingFields.forEach(name => {
                if (fields[name]) form.elements[name].value = fields[name];
            });
            status.textContent = missingFields.every(name => form.elements[name].value.trim())
                ? 'Đã nạp đầy đủ thông tin địa chỉ.'
                : 'Một vài thông tin chưa có trong địa chỉ cũ. Bạn có thể chọn vị trí trên bản đồ để tự điền.';
        } catch (error) {
            status.textContent = 'Không tự nhận diện được địa chỉ cũ. Hãy chọn vị trí trên bản đồ để điền các phần còn thiếu.';
        }
    }

    function openAddressMap(form) {
        activeAddressForm = form;
        resolvedMapAddress = null;
        addressMapPoint = null;
        document.getElementById('address-map-status').textContent = 'Tìm kiếm hoặc nhấn lên bản đồ để chọn vị trí.';
        document.getElementById('address-map-dialog').showModal();

        window.setTimeout(() => {
            if (!window.L) {
                document.getElementById('address-map-status').textContent = 'Không tải được bản đồ. Kiểm tra kết nối rồi thử lại.';
                return;
            }

            if (!addressMap) {
                const startPoint = [21.0074, 105.8248];
                addressMap = L.map('address-map-canvas').setView(startPoint, 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(addressMap);
                addressMapMarker = L.marker(startPoint, { draggable: true }).addTo(addressMap);
                addressMap.on('click', event => selectAddressMapPoint(event.latlng));
                addressMapMarker.on('dragend', event => selectAddressMapPoint(event.target.getLatLng()));
            }

            addressMap.invalidateSize();
        }, 120);
    }

    async function searchAddressOnMap() {
        const query = document.getElementById('address-map-query').value.trim();
        if (!query) return;
        document.getElementById('address-map-status').textContent = 'Đang tìm vị trí...';

        try {
            const params = new URLSearchParams({ q: query, format: 'jsonv2', addressdetails: '1', limit: '1', countrycodes: 'vn' });
            const response = await fetch(`https://nominatim.openstreetmap.org/search?${params}`);
            if (!response.ok) throw new Error('Map search failed');
            const results = await response.json();
            if (!results.length) {
                document.getElementById('address-map-status').textContent = 'Không tìm thấy địa chỉ, hãy thử từ khóa khác.';
                return;
            }

            const result = results[0];
            const point = L.latLng(Number(result.lat), Number(result.lon));
            addressMap.setView(point, 17);
            addressMapMarker.setLatLng(point);
            await resolveAddressMapPoint(point);
        } catch (error) {
            document.getElementById('address-map-status').textContent = 'Không thể tìm vị trí lúc này. Vui lòng thử lại.';
        }
    }

    async function selectAddressMapPoint(point) {
        addressMapMarker.setLatLng(point);
        await resolveAddressMapPoint(point);
    }

    async function resolveAddressMapPoint(point) {
        addressMapPoint = point;
        resolvedMapAddress = null;
        document.getElementById('address-map-status').textContent = 'Đang lấy thông tin địa chỉ...';

        try {
            const params = new URLSearchParams({ lat: point.lat, lon: point.lng, format: 'jsonv2', addressdetails: '1', zoom: '18' });
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?${params}`);
            if (!response.ok) throw new Error('Reverse geocoding failed');
            const result = await response.json();
            if (!result.address) throw new Error('Address not found');

            resolvedMapAddress = result;
            document.getElementById('address-map-status').textContent = result.display_name;
        } catch (error) {
            document.getElementById('address-map-status').textContent = 'Không lấy được địa chỉ tại vị trí này. Hãy chọn điểm khác.';
        }
    }

    function applyAddressMapSelection() {
        if (!activeAddressForm || !resolvedMapAddress?.address) {
            document.getElementById('address-map-status').textContent = 'Hãy chọn một vị trí hợp lệ trên bản đồ trước.';
            return;
        }

        const fieldValues = mappedAddressFields(resolvedMapAddress.address, resolvedMapAddress);

        Object.entries(fieldValues).forEach(([name, value]) => {
            if (value) {
                const field = activeAddressForm.querySelector(`[name="${name}"]`);
                if (field) field.value = value;
            }
        });

        document.getElementById('address-map-dialog').close();
    }
</script>
@endpush
@endsection