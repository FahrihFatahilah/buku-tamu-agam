<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class AppearanceController extends Controller
{
    public function __construct(private AuditLogService $audit) {}

    public function index(Wedding $wedding)
    {
        $this->authorize('update', $wedding);
        return view('admin.appearance.index', compact('wedding'));
    }

    public function update(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $validated = $request->validate([
            'appearance'                    => 'nullable|array',
            'appearance.primary_color'      => 'nullable|string|max:20',
            'appearance.secondary_color'    => 'nullable|string|max:20',
            'appearance.accent_color'       => 'nullable|string|max:20',
            'appearance.bg_color'           => 'nullable|string|max:20',
            'appearance.font_display'       => 'nullable|string|max:100',
            'appearance.font_body'          => 'nullable|string|max:100',
            'appearance.terminology'        => 'nullable|array',
            'animation_config'              => 'nullable|array',
            'animation_config.preset'       => 'nullable|in:elegant,cinematic,romantic,traditional,minimal,none',
            'animation_config.duration'     => 'nullable|in:fast,normal,slow',
            'animation_config.intensity'    => 'nullable|in:subtle,normal,strong',
            'bg_image'                      => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $appearance = array_merge($wedding->appearance ?? [], $validated['appearance'] ?? []);

        if ($request->hasFile('bg_image')) {
            $path = $request->file('bg_image')->storeAs(
                "weddings/{$wedding->id}/appearance",
                'bg.' . $request->file('bg_image')->getClientOriginalExtension(),
                'public'
            );
            $appearance['bg_image'] = $path;
        }

        $wedding->update([
            'appearance'       => $appearance,
            'animation_config' => array_merge($wedding->animation_config ?? [], $validated['animation_config'] ?? []),
        ]);

        $this->audit->log('wedding.appearance_updated', 'wedding', $wedding->id, [], $wedding->id);

        return back()->with('success', 'Tampilan berhasil disimpan.');
    }

    public function updateSettings(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $validated = $request->validate([
            'settings'                          => 'nullable|array',
            'settings.guestbook_moderation'     => 'boolean',
            'settings.rsvp_enabled'             => 'boolean',
            'settings.guestbook_enabled'        => 'boolean',
            'settings.show_guest_count'         => 'boolean',
        ]);

        $current  = $wedding->settings ?? [];
        $new      = array_merge($current, $validated['settings'] ?? []);
        $wedding->update(['settings' => $new]);

        $this->audit->log('wedding.settings_updated', 'wedding', $wedding->id, [], $wedding->id);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
