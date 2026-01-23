<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\AuditLogger;
use App\Support\OutletContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('outlet_id', OutletContext::id())->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
        ]);

        $category = Category::create([
            'outlet_id' => OutletContext::id(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'is_active' => true,
        ]);

        AuditLogger::log('category_created', Category::class, $category->id, null, $category->toArray());

        return redirect()->route('categories.index');
    }

    public function update(Request $request, Category $category)
    {
        if ($category->outlet_id !== OutletContext::id()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $category->toArray();
        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        AuditLogger::log('category_updated', Category::class, $category->id, $before, $category->toArray());

        return redirect()->route('categories.index');
    }
}
