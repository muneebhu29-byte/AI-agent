@extends('layouts.store')

@section('title', 'Order')

@section('content')
<h1 class="text-2xl font-bold mb-4">Order {{ $order->order_number }}</h1>
@foreach($order->items as $item)
    <div>{{ $item->product_name_snapshot }} x {{ $item->quantity }}</div>
@endforeach
@endsection
