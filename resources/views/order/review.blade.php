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
    <form action="{{ route('orders.review.store', $order->id) }}" method="POST" enctype="multipart/form-data" id="review-form">
        @csrf
        <fieldset>
            <legend>Số sao</legend>
            @for($rating = 1; $rating <= 5; $rating++)
                <label><input type="radio" name="rating" value="{{ $rating }}" required @checked(old('rating') == $rating)> {{ $rating }} sao</label>
            @endfor
        </fieldset>
        <p><label for="review-comment">Nhận xét</label>
            <textarea id="review-comment" name="comment" maxlength="500" rows="5" style="display:block;width:100%">{{ old('comment') }}</textarea>
            <small id="comment-requirement">Nhận xét bắt buộc với đánh giá từ 1 đến 3 sao.</small>
        </p>
        <p><label for="review-images">Ảnh minh họa (tối đa 3 ảnh, JPG/PNG, tối đa 5 MB mỗi ảnh)</label>
            <input id="review-images" type="file" name="images[]" accept="image/jpeg,image/png" multiple>
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
    function syncCommentRequirement() {
        const selected = document.querySelector('input[name="rating"]:checked');
        commentInput.required = selected && Number(selected.value) <= 3;
    }
    ratingInputs.forEach(input => input.addEventListener('change', syncCommentRequirement));
    document.getElementById('review-form').addEventListener('submit', event => {
        const files = document.getElementById('review-images').files;
        if (files.length > 3 || [...files].some(file => file.size > 5 * 1024 * 1024)) {
            event.preventDefault();
            alert('Chọn tối đa 3 ảnh, mỗi ảnh không quá 5 MB.');
        }
    });
    syncCommentRequirement();
</script>
@endpush