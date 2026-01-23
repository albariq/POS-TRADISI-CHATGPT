@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">Categories</h1>

<form method="POST" action="{{ route('categories.store') }}" class="flex gap-2 mb-4">
    @csrf
    <input name="name" class="border border-slate-300 rounded px-3 py-2 flex-1" placeholder="New category">
    <button class="bg-slate-900 text-white rounded px-3">Add</button>
</form>

<div class="bg-white rounded shadow p-4 space-y-3">
    @foreach ($categories as $category)
        <form method="POST" action="{{ route('categories.update', $category) }}" class="flex gap-2 items-center">
            @csrf
            @method('PUT')
            <input name="name" class="border border-slate-300 rounded px-3 py-2 flex-1" value="{{ $category->name }}" placeholder="Category name">
            <label class="text-xs"><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> Active</label>
            <button class="text-sm bg-slate-200 rounded px-2">Save</button>
        </form>
    @endforeach
</div>
@endsection
