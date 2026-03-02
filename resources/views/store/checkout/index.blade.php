@extends('layouts.store')

@section('title', 'Checkout')

@section('content')
<h1 class="text-2xl font-bold mb-4">Checkout</h1>
<form method="POST" action="{{ route('checkout.session') }}">
    @csrf
    <x-ui.button>Pay with Stripe</x-ui.button>
</form>
@endsection
