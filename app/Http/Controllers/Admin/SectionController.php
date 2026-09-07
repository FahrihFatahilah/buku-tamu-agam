<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\WeddingSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SectionController extends Controller
{
    public function index(Wedding $wedding)
    {
        $this->authorize('update', $wedding);
        $sections = $wedding->sections()->orderBy('sort_order')->get();
        return view('admin.sections.index', compact('wedding', 'sections'));
    }

    public function update(Request $request, Wedding $wedding, WeddingSection $section)
    {
        $this->authorize('update', $wedding);
        abort_if($section->wedding_id !== $wedding->id, 403);

        $validated = $request->validate([
            'title'      => 'nullable|string|max:100',
            'is_enabled' => 'nullable',
            'sort_order' => 'integer',
            'settings'   => 'nullable|array',
        ]);

        $settings = $section->settings ?? [];
        $newSettings = array_merge($settings, $validated['settings'] ?? []);

        // Handle bg_image upload
        if ($request->hasFile('bg_image_file')) {
            $request->validate(['bg_image_file' => 'file|max:5120']);
            if (!empty($settings['bg_image'])) {
                Storage::disk('public')->delete($settings['bg_image']);
            }
            $path = $request->file('bg_image_file')->storeAs(
                "weddings/{$wedding->id}/sections",
                Str::uuid() . '.' . $request->file('bg_image_file')->getClientOriginalExtension(),
                'public'
            );
            $newSettings['bg_image'] = $path;
        }

        if ($request->boolean('settings.remove_bg_image') && !empty($settings['bg_image'])) {
            Storage::disk('public')->delete($settings['bg_image']);
            unset($newSettings['bg_image']);
        }

        // Handle overlay_image upload
        if ($request->hasFile('overlay_image_file')) {
            $request->validate(['overlay_image_file' => 'file|max:5120']);
            if (!empty($settings['overlay_image'])) {
                Storage::disk('public')->delete($settings['overlay_image']);
            }
            $path = $request->file('overlay_image_file')->storeAs(
                "weddings/{$wedding->id}/sections",
                Str::uuid() . '.' . $request->file('overlay_image_file')->getClientOriginalExtension(),
                'public'
            );
            $newSettings['overlay_image'] = $path;
        }

        if ($request->boolean('settings.remove_overlay_image') && !empty($settings['overlay_image'])) {
            Storage::disk('public')->delete($settings['overlay_image']);
            unset($newSettings['overlay_image']);
        }

        // Convert overlay_img_opacity_pct (0-100) to float (0-1)
        if (isset($newSettings['overlay_img_opacity_pct'])) {
            $newSettings['overlay_img_opacity'] = (int)$newSettings['overlay_img_opacity_pct'] / 100;
            unset($newSettings['overlay_img_opacity_pct']);
        }

        // Clean up remove flags
        unset($newSettings['remove_bg_image'], $newSettings['remove_overlay_image']);

        $section->update([
            'title'      => $validated['title'] ?? $section->title,
            'is_enabled' => $request->has('is_enabled') ? (bool)$request->input('is_enabled') : $section->is_enabled,
            'sort_order' => $validated['sort_order'] ?? $section->sort_order,
            'settings'   => $newSettings,
        ]);

        return back()->with('success', 'Section berhasil diperbarui.');
    }

    public function reorder(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer',
        ]);

        foreach ($request->order as $sort => $id) {
            WeddingSection::where('id', $id)->where('wedding_id', $wedding->id)
                ->update(['sort_order' => $sort]);
        }

        return response()->json(['ok' => true]);
    }
}
