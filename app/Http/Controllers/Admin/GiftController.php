<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftMethod;
use App\Models\VisibilityRule;
use App\Models\Wedding;
use App\Services\MediaService;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    public function __construct(private MediaService $mediaService) {}

    public function index(Wedding $wedding)
    {
        $this->authorize('update', $wedding);
        $gifts = $wedding->giftMethods()->orderBy('sort_order')->get();
        $categories = $wedding->guestCategories;
        $rules = VisibilityRule::where('wedding_id', $wedding->id)
            ->where('entity_type', 'gift_method')
            ->get()
            ->groupBy('entity_id');
        return view('admin.gift.index', compact('wedding', 'gifts', 'categories', 'rules'));
    }

    public function updateVisibility(Request $request, Wedding $wedding, GiftMethod $gift)
    {
        $this->authorize('update', $wedding);
        abort_if($gift->wedding_id !== $wedding->id, 403);

        $request->validate([
            'scope'      => 'required|in:wedding_default,category',
            'scope_id'   => 'nullable|integer',
            'is_visible' => 'required|boolean',
        ]);

        VisibilityRule::updateOrCreate(
            [
                'wedding_id'  => $wedding->id,
                'entity_type' => 'gift_method',
                'entity_id'   => $gift->id,
                'scope'       => $request->scope,
                'scope_id'    => $request->scope_id,
            ],
            ['is_visible' => $request->boolean('is_visible')]
        );

        return response()->json(['ok' => true]);
    }

    public function store(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $validated = $request->validate([
            'type'           => 'required|in:bank_transfer,qris,e_wallet,cash,custom',
            'label'          => 'required|string|max:100',
            'bank_name'      => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:100',
            'merchant_name'  => 'nullable|string|max:100',
            'description'    => 'nullable|string|max:300',
            'is_active'      => 'boolean',
            'sort_order'     => 'integer',
            'image'          => 'nullable|file|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->mediaService->storeFile(
                $request->file('image'), "weddings/{$wedding->id}/gift"
            );
        }

        $wedding->giftMethods()->create($validated);

        return back()->with('success', 'Metode hadiah berhasil ditambahkan.');
    }

    public function update(Request $request, Wedding $wedding, GiftMethod $gift)
    {
        $this->authorize('update', $wedding);
        abort_if($gift->wedding_id !== $wedding->id, 403);

        $validated = $request->validate([
            'type'           => 'required|in:bank_transfer,qris,e_wallet,cash,custom',
            'label'          => 'required|string|max:100',
            'bank_name'      => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:100',
            'merchant_name'  => 'nullable|string|max:100',
            'description'    => 'nullable|string|max:300',
            'is_active'      => 'boolean',
            'sort_order'     => 'integer',
            'image'          => 'nullable|file|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->mediaService->storeFile(
                $request->file('image'), "weddings/{$wedding->id}/gift"
            );
        }

        $gift->update($validated);

        return back()->with('success', 'Metode hadiah berhasil diperbarui.');
    }

    public function destroy(Wedding $wedding, GiftMethod $gift)
    {
        $this->authorize('update', $wedding);
        abort_if($gift->wedding_id !== $wedding->id, 403);

        $gift->delete();

        return back()->with('success', 'Metode hadiah berhasil dihapus.');
    }
}
