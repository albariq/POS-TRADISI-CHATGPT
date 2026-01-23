<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Support\AuditLogger;
use App\Support\OutletContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::where('outlet_id', OutletContext::id())->orderBy('name')->get();
        return view('tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
        ]);

        $tag = Tag::create([
            'outlet_id' => OutletContext::id(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        AuditLogger::log('tag_created', Tag::class, $tag->id, null, $tag->toArray());

        return redirect()->route('tags.index');
    }

    public function update(Request $request, Tag $tag)
    {
        if ($tag->outlet_id !== OutletContext::id()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string'],
        ]);

        $before = $tag->toArray();
        $tag->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        AuditLogger::log('tag_updated', Tag::class, $tag->id, $before, $tag->toArray());

        return redirect()->route('tags.index');
    }
}
