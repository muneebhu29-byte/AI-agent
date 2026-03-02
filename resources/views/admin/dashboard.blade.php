@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<h1 class="text-2xl font-bold mb-4">Dashboard</h1>
<div class="grid md:grid-cols-3 gap-4">
    <div class="border rounded p-4 bg-white">Orders: {{ $metrics['orders'] }}</div>
    <div class="border rounded p-4 bg-white">Revenue: ${{ number_format($metrics['revenue'], 2) }}</div>
    <div class="border rounded p-4 bg-white">Low stock SKUs: {{ $metrics['low_stock'] }}</div>
</div>
@endsection
