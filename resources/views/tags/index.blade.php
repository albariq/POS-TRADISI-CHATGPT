@extends('layouts.app')

@section('content')
<h1 class="text-xl font-semibold mb-4">Tags</h1>

<form method="POST" action="{{ route('tags.store') }}" class="flex gap-2 mb-4">
    @csrf
    <input name="name" class="border border-slate-300 rounded px-3 py-2 flex-1" placeholder="New tag">
    <button class="bg-slate-900 text-white rounded px-3">Add</button>
</form>

<div class="bg-white rounded shadow p-4 space-y-3">
    @foreach ($tags as $tag)
        <form method="POST" action="{{ route('tags.update', $tag) }}" class="flex gap-2 items-center">
            @csrf
            @method('PUT')
            <input name="name" class="border border-slate-300 rounded px-3 py-2 flex-1" value="{{ $tag->name }}" placeholder="Tag name">
            <button class="text-sm bg-slate-200 rounded px-2">Save</button>
        </form>
    @endforeach
</div>
@endsection
