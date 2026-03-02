<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->orderBy('sort_order')->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:categories,slug'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ]);

        Category::create($data + ['is_active' => true, 'sort_order' => 0]);

        return back()->with('success', 'Category created');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:categories,slug,'.$category->id],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $category->update($data);

        return back()->with('success', 'Category updated');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Category deleted');
    }
}
