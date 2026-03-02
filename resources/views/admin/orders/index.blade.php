@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<h1 class="text-2xl font-bold mb-4">Orders</h1>
<table class="w-full bg-white border">
    <tr><th class="p-2">Order #</th><th>Customer</th><th>Status</th><th>Total</th><th></th></tr>
    @foreach($orders as $order)
    <tr class="border-t">
        <td class="p-2">{{ $order->order_number }}</td>
        <td>{{ $order->user->email }}</td>
        <td>{{ $order->status->value ?? $order->status }}</td>
        <td>${{ number_format($order->grand_total, 2) }}</td>
        <td><a class="underline" href="{{ route('admin.orders.show', $order) }}">View</a></td>
    </tr>
    @endforeach
</table>
@endsection
