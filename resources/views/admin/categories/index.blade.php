@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<h1 class="text-2xl font-bold mb-4">Categories</h1>
<table class="w-full bg-white border">
    <tr><th class="p-2">Name</th><th>Slug</th><th>Active</th></tr>
    @foreach($categories as $category)
    <tr class="border-t">
        <td class="p-2">{{ $category->name }}</td>
        <td>{{ $category->slug }}</td>
        <td>{{ $category->is_active ? 'Yes' : 'No' }}</td>
    </tr>
    @endforeach
</table>
@endsection
