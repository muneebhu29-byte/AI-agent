@extends('layouts.store')

@section('title', 'My Orders')

@section('content')
<h1 class="text-2xl font-bold mb-4">My Orders</h1>
@foreach($orders as $order)
<div class="border rounded p-3 mb-2">
    <a class="underline" href="{{ route('account.orders.show', $order) }}">{{ $order->order_number }}</a>
</div>
@endforeach
@endsection
