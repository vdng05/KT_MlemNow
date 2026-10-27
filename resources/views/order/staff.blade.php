@extends('layouts.app')

@section('content')
<section style="max-width:1100px;margin:40px auto;padding:0 18px">
    <h1>Đơn hàng cần xử lý</h1>
    @if($errors->any())<div role="alert" style="color:#b42318">{{ $errors->first() }}</div>@endif
    @foreach($orders as $order)
        <article style="border-top:1px solid #ddd;padding:18px 0">
            <strong>#{{ $order->order_code }}</strong>
            <span>{{ $order->getStateLabel() }}</span>
            <span>{{ $order->customer_name }} · {{ $order->delivery_address }}</span>
            <ul>@foreach($order->items as $item)<li>{{ $item->quantity }} × {{ $item->food_name }}</li>@endforeach</ul>
            @php
                $nextStatus = ['pending' => 'confirmed', 'confirmed' => 'preparing', 'preparing' => 'delivering', 'delivering' => 'completed'][$order->status] ?? null;
                $nextStatusLabels = ['confirmed' => 'Đã xác nhận', 'preparing' => 'Đang chuẩn bị', 'delivering' => 'Đang giao', 'completed' => 'Đã giao'];
            @endphp
            @if($nextStatus)
                <form action="{{ route('staff.orders.status', $order->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="{{ $nextStatus }}">
                    <button type="submit">Chuyển sang {{ $nextStatusLabels[$nextStatus] }}</button>
                </form>
            @endif
        </article>
    @endforeach
</section>
@endsection