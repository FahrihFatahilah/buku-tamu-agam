<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestCategory;
use App\Models\Wedding;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Wedding $wedding)
    {
        $this->authorize('manageGuests', $wedding);
        $categories = $wedding->guestCategories()->withCount('guests')->get();
        return view('admin.categories.index', compact('wedding', 'categories'));
    }

    public function store(Request $request, Wedding $wedding)
    {
        $this->authorize('manageGuests', $wedding);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'sort_order'  => 'integer',
        ]);

        $wedding->guestCategories()->create($validated);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Wedding $wedding, GuestCategory $category)
    {
        $this->authorize('manageGuests', $wedding);
        abort_if($category->wedding_id !== $wedding->id, 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'sort_order'  => 'integer',
        ]);

        $category->update($validated);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Wedding $wedding, GuestCategory $category)
    {
        $this->authorize('manageGuests', $wedding);
        abort_if($category->wedding_id !== $wedding->id, 403);

        // Move guests to uncategorized
        $category->guests()->update(['category_id' => null]);
        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
