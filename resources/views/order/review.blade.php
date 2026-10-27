@extends('layouts.app')

@section('content')
<section style="max-width:720px;margin:40px auto;padding:0 18px">
    <h1>Đánh giá đơn hàng #{{ $order->order_code }}</h1>
    @if($errors->any())
        <div role="alert" style="color:#b42318;margin-bottom:16px">{{ $errors->first() }}</div>
    @endif
    <ul>
        @foreach($order->items as $item)
            <li>{{ $item->quantity }} × {{ $item->food_name }}</li>
        @endforeach
    </ul>
    <form action="{{ route('orders.review.store', $order->id) }}" method="POST" enctype="multipart/form-data" id="review-form" novalidate>
        @csrf
        <fieldset>
            <legend>Số sao</legend>
            @for($rating = 1; $rating <= 5; $rating++)
                <label><input type="radio" name="rating" value="{{ $rating }}" @checked(old('rating') == $rating)> {{ $rating }} sao</label>
            @endfor
        </fieldset>
        <div id="rating-error" role="alert" style="color:#b42318">@error('rating'){{ $message }}@enderror</div>
        <p><label for="review-comment">Nhận xét</label>
            <textarea id="review-comment" name="comment" maxlength="500" rows="5" style="display:block;width:100%">{{ old('comment') }}</textarea>
            <small id="comment-requirement">Nhận xét bắt buộc với đánh giá từ 1 đến 3 sao.</small>
            <small id="comment-error" role="alert" style="display:block;color:#b42318">@error('comment'){{ $message }}@enderror</small>
        </p>
        <p><label for="review-images">Ảnh minh họa (tối đa 3 ảnh, JPG/PNG, tối đa 5 MB mỗi ảnh)</label>
            <input id="review-images" type="file" name="images[]" accept="image/jpeg,image/png" multiple>
            <small id="images-error" role="alert" style="display:block;color:#b42318">@error('images'){{ $message }}@enderror @error('images.0'){{ $message }}@enderror</small>
        </p>
        <button type="submit">Gửi đánh giá</button>
        <a href="{{ route('orders.history') }}">Hủy</a>
    </form>
</section>
@endsection

@push('scripts')
<script>
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    const commentInput = document.getElementById('review-comment');
    const ratingError = document.getElementById('rating-error');
    const commentError = document.getElementById('comment-error');
    const imagesError = document.getElementById('images-error');
    function syncCommentRequirement() {
        const selected = document.querySelector('input[name="rating"]:checked');
        commentInput.required = selected && Number(selected.value) <= 3;
    }
    ratingInputs.forEach(input => input.addEventListener('change', syncCommentRequirement));
    document.getElementById('review-form').addEventListener('submit', event => {
        const selected = document.querySelector('input[name="rating"]:checked');
        const files = document.getElementById('review-images').files;
        const comment = commentInput.value;
        let isValid = true;

        ratingError.textContent = '';
        commentError.textContent = '';
        imagesError.textContent = '';

        if (!selected) {
            ratingError.textContent = 'Vui lòng chọn số sao đánh giá.';
            isValid = false;
        }

        if (selected && Number(selected.value) <= 3 && !comment.trim()) {
            commentError.textContent = 'Vui lòng nhập nội dung nhận xét cho đánh giá dưới 4 sao.';
            isValid = false;
        } else if (comment && !comment.trim()) {
            commentError.textContent = 'Nội dung nhận xét không được chỉ gồm khoảng trắng.';
            isValid = false;
        } else if (comment.length > 500) {
            commentError.textContent = 'Nội dung nhận xét không được vượt quá 500 ký tự.';
            isValid = false;
        }

        if (files.length > 3) {
            imagesError.textContent = 'Chỉ được tải tối đa 3 ảnh.';
            isValid = false;
        } else if ([...files].some(file => !['image/jpeg', 'image/png'].includes(file.type))) {
            imagesError.textContent = 'Chỉ chấp nhận ảnh JPG, JPEG hoặc PNG.';
            isValid = false;
        } else if ([...files].some(file => file.size > 5 * 1024 * 1024)) {
            imagesError.textContent = 'Mỗi ảnh không được vượt quá 5 MB.';
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
    syncCommentRequirement();
</script>
@endpush