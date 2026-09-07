<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\GuestCheckin;
use App\Services\WeddingService;
use Illuminate\Http\Request;

class WeddingController extends Controller
{
    public function __construct(private WeddingService $weddingService) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $weddings = Wedding::with(['client', 'template'])
            ->when(!$user->isSuperAdmin(), fn($q) => $q->forClient($user->client_id))
            ->latest()
            ->paginate(20);

        return view('admin.weddings.index', compact('weddings'));
    }

    public function create()
    {
        $this->authorize('create', Wedding::class);
        return view('admin.weddings.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Wedding::class);

        $validated = $request->validate([
            'groom_name' => 'required|string|max:100',
            'bride_name' => 'required|string|max:100',
            'title' => 'nullable|string|max:200',
            'date' => 'nullable|date',
            'venue' => 'nullable|string|max:200',
        ]);

        $clientId = $request->user()->isSuperAdmin()
            ? $request->input('client_id', $request->user()->client_id)
            : $request->user()->client_id;

        $wedding = $this->weddingService->create($clientId, $validated);

        return redirect()->route('admin.weddings.edit', $wedding)->with('success', 'Undangan berhasil dibuat.');
    }

    public function edit(Wedding $wedding)
    {
        $this->authorize('update', $wedding);
        $wedding->load(['template', 'sections', 'events', 'domains']);
        $templates = \App\Models\Template::active()->get();

        // Dashboard stats
        $stats = [
            'guests'    => $wedding->guests()->count(),
            'rsvp_yes'  => \App\Models\Rsvp::where('wedding_id', $wedding->id)->where('attendance_status', 'attending')->count(),
            'checkins'  => \App\Models\GuestCheckin::where('wedding_id', $wedding->id)->count(),
            'pending_messages' => $wedding->guestBookEntries()->where('status', 'pending')->count(),
        ];

        return view('admin.weddings.edit', compact('wedding', 'templates', 'stats'));
    }

    public function update(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $validated = $request->validate([
            'groom_name'      => 'sometimes|string|max:100',
            'bride_name'      => 'sometimes|string|max:100',
            'groom_nickname'  => 'nullable|string|max:100',
            'bride_nickname'  => 'nullable|string|max:100',
            'groom_father'    => 'nullable|string|max:100',
            'groom_mother'    => 'nullable|string|max:100',
            'bride_father'    => 'nullable|string|max:100',
            'bride_mother'    => 'nullable|string|max:100',
            'title'           => 'nullable|string|max:200',
            'date'            => 'nullable|date',
            'venue'           => 'nullable|string|max:200',
            'address'         => 'nullable|string|max:500',
            'description'     => 'nullable|string|max:2000',
            'quote'           => 'nullable|string|max:500',
            'slug'            => 'nullable|string|max:100|regex:/^[a-z0-9\-]+$/',
            'seo_title'       => 'nullable|string|max:200',
            'seo_description' => 'nullable|string|max:500',
            'template_id'     => 'nullable|exists:templates,id',
            'og_image'        => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'favicon'         => 'nullable|file|mimes:ico,png|max:512',
        ]);

        if ($request->hasFile('og_image')) {
            $validated['og_image'] = $request->file('og_image')
                ->store("weddings/{$wedding->id}/seo", 'public');
        } else {
            unset($validated['og_image']);
        }

        if ($request->hasFile('favicon')) {
            $validated['favicon'] = $request->file('favicon')
                ->store("weddings/{$wedding->id}/seo", 'public');
        } else {
            unset($validated['favicon']);
        }

        $this->weddingService->update($wedding, $validated);

        return back()->with('success', 'Undangan berhasil diperbarui.');
    }

    public function publish(Wedding $wedding)
    {
        $this->authorize('publish', $wedding);
        $this->weddingService->publish($wedding);
        return back()->with('success', 'Undangan berhasil dipublikasikan.');
    }

    public function unpublish(Wedding $wedding)
    {
        $this->authorize('publish', $wedding);
        $this->weddingService->unpublish($wedding);
        return back()->with('success', 'Undangan berhasil di-unpublish.');
    }

    public function archive(Wedding $wedding)
    {
        $this->authorize('publish', $wedding);
        $this->weddingService->archive($wedding);
        return back()->with('success', 'Undangan berhasil diarsipkan.');
    }

    public function preview(Wedding $wedding)
    {
        $this->authorize('update', $wedding);
        session(["preview_wedding_{$wedding->id}" => true]);
        return redirect($wedding->publicUrl());
    }

    public function previewPage(Wedding $wedding)
    {
        $this->authorize('update', $wedding);
        session(["preview_wedding_{$wedding->id}" => true]);
        return view('admin.weddings.preview', compact('wedding'));
    }

    public function destroy(Wedding $wedding)
    {
        $this->authorize('delete', $wedding);
        $wedding->delete();
        return redirect()->route('admin.weddings.index')->with('success', 'Undangan berhasil dihapus.');
    }
}
