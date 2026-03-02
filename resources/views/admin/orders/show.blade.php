@extends('layouts.admin')

@section('title', 'Order Details')

@section('content')
<h1 class="text-2xl font-bold mb-4">Order {{ $order->order_number }}</h1>
<p>Status: {{ $order->status->value ?? $order->status }}</p>
<p>Payment: {{ $order->payment_status->value ?? $order->payment_status }}</p>
@endsection
