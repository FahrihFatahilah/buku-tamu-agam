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
            'is_enabled' => $request->has('is_enabled') ? $request->boolean('is_enabled') : $section->is_enabled,
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

    public function timelineIndex(Wedding $wedding, WeddingSection $section)
    {
        $this->authorize('update', $wedding);
        abort_if($section->wedding_id !== $wedding->id || $section->section_key !== 'timeline', 403);
        $items = $section->settings['items'] ?? [];
        return view('admin.sections.timeline', compact('wedding', 'section', 'items'));
    }

    public function timelineStore(Request $request, Wedding $wedding, WeddingSection $section)
    {
        $this->authorize('update', $wedding);
        abort_if($section->wedding_id !== $wedding->id, 403);

        $validated = $request->validate([
            'time'        => 'required|string|max:50',
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $items = $section->settings['items'] ?? [];
        $items[] = $validated;
        $section->update(['settings' => array_merge($section->settings ?? [], ['items' => $items])]);

        return back()->with('success', 'Item timeline berhasil ditambahkan.');
    }

    public function timelineUpdate(Request $request, Wedding $wedding, WeddingSection $section, int $index)
    {
        $this->authorize('update', $wedding);
        abort_if($section->wedding_id !== $wedding->id, 403);

        $validated = $request->validate([
            'time'        => 'required|string|max:50',
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $items = $section->settings['items'] ?? [];
        abort_if(!isset($items[$index]), 404);
        $items[$index] = $validated;
        $section->update(['settings' => array_merge($section->settings ?? [], ['items' => array_values($items)])]);

        return back()->with('success', 'Item timeline berhasil diperbarui.');
    }

    public function timelineDestroy(Wedding $wedding, WeddingSection $section, int $index)
    {
        $this->authorize('update', $wedding);
        abort_if($section->wedding_id !== $wedding->id, 403);

        $items = $section->settings['items'] ?? [];
        array_splice($items, $index, 1);
        $section->update(['settings' => array_merge($section->settings ?? [], ['items' => array_values($items)])]);

        return back()->with('success', 'Item timeline berhasil dihapus.');
    }

    public function loveStoryIndex(Wedding $wedding, WeddingSection $section)
    {
        $this->authorize('update', $wedding);
        abort_if($section->wedding_id !== $wedding->id || $section->section_key !== 'love_story', 403);
        $stories = $section->settings['stories'] ?? [];
        return view('admin.sections.love-story', compact('wedding', 'section', 'stories'));
    }

    public function loveStoryStore(Request $request, Wedding $wedding, WeddingSection $section)
    {
        $this->authorize('update', $wedding);
        abort_if($section->wedding_id !== $wedding->id, 403);

        $validated = $request->validate([
            'year'        => 'required|string|max:50',
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        $stories = $section->settings['stories'] ?? [];
        $stories[] = $validated;
        $section->update(['settings' => array_merge($section->settings ?? [], ['stories' => $stories])]);

        return back()->with('success', 'Kisah berhasil ditambahkan.');
    }

    public function loveStoryUpdate(Request $request, Wedding $wedding, WeddingSection $section, int $index)
    {
        $this->authorize('update', $wedding);
        abort_if($section->wedding_id !== $wedding->id, 403);

        $validated = $request->validate([
            'year'        => 'required|string|max:50',
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        $stories = $section->settings['stories'] ?? [];
        abort_if(!isset($stories[$index]), 404);
        $stories[$index] = $validated;
        $section->update(['settings' => array_merge($section->settings ?? [], ['stories' => array_values($stories)])]);

        return back()->with('success', 'Kisah berhasil diperbarui.');
    }

    public function loveStoryDestroy(Wedding $wedding, WeddingSection $section, int $index)
    {
        $this->authorize('update', $wedding);
        abort_if($section->wedding_id !== $wedding->id, 403);

        $stories = $section->settings['stories'] ?? [];
        array_splice($stories, $index, 1);
        $section->update(['settings' => array_merge($section->settings ?? [], ['stories' => array_values($stories)])]);

        return back()->with('success', 'Kisah berhasil dihapus.');
    }
}
