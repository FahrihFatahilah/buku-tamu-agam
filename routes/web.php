<?php

use App\Http\Controllers\Admin\AppearanceController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\GiftController;
use App\Http\Controllers\Admin\GuestBookController as AdminGuestBookController;
use App\Http\Controllers\Admin\GuestController;
use App\Http\Controllers\Admin\InvitationBuilderController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PlaylistController;
use App\Http\Controllers\Admin\QrController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisibilityController;
use App\Http\Controllers\Admin\WeddingController;
use App\Http\Controllers\CheckIn\CheckInController;
use App\Http\Controllers\Public\GuestBookController;
use App\Http\Controllers\Public\InvitationController;
use App\Http\Controllers\Public\RsvpController;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', fn () => redirect()->route('admin.weddings.index'));

// Health check — no sensitive data exposed
Route::get('/health', function () {
    $checks = ['status' => 'ok', 'timestamp' => now()->toISOString()];

    // DB check
    try {
        DB::connection()->getPdo();
        $checks['database'] = 'ok';
    } catch (Exception) {
        $checks['database'] = 'error';
        $checks['status'] = 'degraded';
    }

    // Storage check
    try {
        Storage::disk('public')->exists('.gitignore');
        $checks['storage'] = 'ok';
    } catch (Exception) {
        $checks['storage'] = 'error';
        $checks['status'] = 'degraded';
    }

    // Queue backlog check (database driver only — no Redis required)
    try {
        $pending = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')->count();
        $checks['queue'] = ['pending' => $pending, 'failed' => $failed];
        if ($failed > 10) {
            $checks['status'] = 'degraded';
        }
    } catch (Exception) {
        $checks['queue'] = 'unavailable';
    }

    $httpStatus = $checks['status'] === 'ok' ? 200 : 503;

    return response()->json($checks, $httpStatus);
});

// Auth routes
require __DIR__.'/auth.php';

// ─── Admin ───────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    Route::get('/', fn () => redirect()->route('admin.weddings.index'));

    // Super Admin only
    Route::middleware('can:viewAny,App\Models\Client')->group(function () {
        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
        Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
        Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        // User management per client
        Route::get('clients/{client}/users', [UserController::class, 'index'])->name('clients.users.index');
        Route::post('clients/{client}/users', [UserController::class, 'store'])->name('clients.users.store');
        Route::put('clients/{client}/users/{user}', [UserController::class, 'update'])->name('clients.users.update');
        Route::delete('clients/{client}/users/{user}', [UserController::class, 'destroy'])->name('clients.users.destroy');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    });

    // Templates — Super Admin only (spec §4)
    Route::middleware('can:viewAny,App\Models\Template')->group(function () {
        Route::get('templates/preview', [TemplateController::class, 'preview'])->name('templates.preview');
        Route::resource('templates', TemplateController::class)->except(['show']);
    });

    // Weddings
    Route::resource('weddings', WeddingController::class)->except(['show']);
    Route::post('weddings/{wedding}/publish', [WeddingController::class, 'publish'])->name('weddings.publish');
    Route::post('weddings/{wedding}/unpublish', [WeddingController::class, 'unpublish'])->name('weddings.unpublish');
    Route::post('weddings/{wedding}/archive', [WeddingController::class, 'archive'])->name('weddings.archive');
    Route::get('weddings/{wedding}/preview', [WeddingController::class, 'preview'])->name('weddings.preview');
    Route::get('weddings/{wedding}/preview-page', [WeddingController::class, 'previewPage'])->name('weddings.preview-page');

    // All wedding-scoped routes
    Route::prefix('weddings/{wedding}')->name('weddings.')->group(function () {
        // Appearance, Animations & Settings
        Route::get('appearance', [AppearanceController::class, 'index'])->name('appearance.index');
        Route::post('appearance', [AppearanceController::class, 'update'])->name('appearance.update');
        Route::post('settings', [AppearanceController::class, 'updateSettings'])->name('settings.update');

        // Guests
        Route::get('guests', [GuestController::class, 'index'])->name('guests.index');
        Route::post('guests', [GuestController::class, 'store'])->name('guests.store');
        Route::put('guests/{guest}', [GuestController::class, 'update'])->name('guests.update');
        Route::delete('guests/{guest}', [GuestController::class, 'destroy'])->name('guests.destroy');
        Route::post('guests/{guest}/regenerate-token', [GuestController::class, 'regenerateToken'])->name('guests.regenerate-token');
        Route::post('guests/import', [GuestController::class, 'importCsv'])->name('guests.import');

        // Guest Categories
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Events
        Route::get('events', [EventController::class, 'index'])->name('events.index');
        Route::post('events', [EventController::class, 'store'])->name('events.store');
        Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

        // Sections
        Route::get('sections', [SectionController::class, 'index'])->name('sections.index');
        Route::put('sections/{section}', [SectionController::class, 'update'])->name('sections.update');
        Route::post('sections/reorder', [SectionController::class, 'reorder'])->name('sections.reorder');
        Route::get('sections/{section}/timeline', [SectionController::class, 'timelineIndex'])->name('sections.timeline.index');
        Route::post('sections/{section}/timeline', [SectionController::class, 'timelineStore'])->name('sections.timeline.store');
        Route::put('sections/{section}/timeline/{index}', [SectionController::class, 'timelineUpdate'])->name('sections.timeline.update');
        Route::delete('sections/{section}/timeline/{index}', [SectionController::class, 'timelineDestroy'])->name('sections.timeline.destroy');
        Route::post('sections/{section}/timeline/reorder', [SectionController::class, 'timelineReorder'])->name('sections.timeline.reorder');
        Route::get('sections/{section}/love-story', [SectionController::class, 'loveStoryIndex'])->name('sections.love-story.index');
        Route::post('sections/{section}/love-story', [SectionController::class, 'loveStoryStore'])->name('sections.love-story.store');
        Route::put('sections/{section}/love-story/{index}', [SectionController::class, 'loveStoryUpdate'])->name('sections.love-story.update');
        Route::delete('sections/{section}/love-story/{index}', [SectionController::class, 'loveStoryDestroy'])->name('sections.love-story.destroy');

        // Media
        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::put('media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
        Route::post('media/reorder', [MediaController::class, 'reorder'])->name('media.reorder');

        // Gift
        Route::get('gift', [GiftController::class, 'index'])->name('gift.index');
        Route::post('gift', [GiftController::class, 'store'])->name('gift.store');
        Route::put('gift/{gift}', [GiftController::class, 'update'])->name('gift.update');
        Route::delete('gift/{gift}', [GiftController::class, 'destroy'])->name('gift.destroy');
        Route::post('gift/{gift}/visibility', [GiftController::class, 'updateVisibility'])->name('gift.visibility');

        // Playlist / Music
        Route::get('playlist', [PlaylistController::class, 'index'])->name('playlist.index');
        Route::post('playlist/settings', [PlaylistController::class, 'updateSettings'])->name('playlist.settings');
        Route::post('playlist/tracks', [PlaylistController::class, 'addTrack'])->name('playlist.tracks.store');
        Route::delete('playlist/tracks/{item}', [PlaylistController::class, 'deleteTrack'])->name('playlist.tracks.destroy');
        Route::post('playlist/reorder', [PlaylistController::class, 'reorder'])->name('playlist.reorder');

        // QR Codes
        Route::get('guests/{guest}/qr', [QrController::class, 'show'])->name('guests.qr');
        Route::get('guests/{guest}/qr/download', [QrController::class, 'download'])->name('guests.qr.download');

        // Visibility Rules
        Route::get('visibility', [VisibilityController::class, 'index'])->name('visibility.index');
        Route::post('visibility', [VisibilityController::class, 'store'])->name('visibility.store');
        Route::delete('visibility/{rule}', [VisibilityController::class, 'destroy'])->name('visibility.destroy');

        // Domains
        Route::get('domains', [DomainController::class, 'index'])->name('domains.index');
        Route::post('domains', [DomainController::class, 'store'])->name('domains.store');
        Route::post('domains/{domain}/verify', [DomainController::class, 'verify'])
            ->name('domains.verify')
            ->middleware('throttle:10,1');
        Route::delete('domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');

        // Guest Book moderation
        Route::get('guestbook', [AdminGuestBookController::class, 'index'])->name('guestbook.index');
        Route::post('guestbook/{entry}/moderate', [AdminGuestBookController::class, 'moderate'])->name('guestbook.moderate');
        Route::post('guestbook/bulk-moderate', [AdminGuestBookController::class, 'bulkModerate'])->name('guestbook.bulk-moderate');
        Route::delete('guestbook/{entry}', [AdminGuestBookController::class, 'destroy'])->name('guestbook.destroy');

        // ─── Visual Page Builder ────────────────────────────────────────────
        // Super-admin gated via WeddingPolicy::buildDocument.
        Route::prefix('builder')->name('builder.')->group(function () {
            Route::get('/', [InvitationBuilderController::class, 'edit'])->name('edit');
            Route::get('preview', [InvitationBuilderController::class, 'preview'])->name('preview');
            // POST stages the editor's unsaved document for the canvas.
            Route::post('preview', [InvitationBuilderController::class, 'stagePreview'])->name('preview.stage');
            Route::get('document', [InvitationBuilderController::class, 'show'])->name('document.show');
            Route::put('document', [InvitationBuilderController::class, 'update'])->name('document.update');
            Route::delete('document', [InvitationBuilderController::class, 'destroy'])->name('document.destroy');
            Route::post('publish', [InvitationBuilderController::class, 'publish'])->name('publish');
            Route::post('enable', [InvitationBuilderController::class, 'enable'])->name('enable');
            Route::post('seed', [InvitationBuilderController::class, 'seed'])->name('seed');
        });
    });
});

// ─── Check-in ────────────────────────────────────────────────────────────────
Route::prefix('check-in')->name('checkin.')->middleware(['auth'])->group(function () {
    Route::get('/', [CheckInController::class, 'index'])->name('index');
    Route::get('{wedding}', [CheckInController::class, 'scanner'])->name('scanner');
    Route::get('{wedding}/token/{token}', [CheckInController::class, 'resolveToken'])->name('resolve-token');
    Route::post('{wedding}/confirm', [CheckInController::class, 'confirm'])->name('confirm');
    Route::get('{wedding}/search', [CheckInController::class, 'search'])->name('search');
});

// ─── Public Invitation ───────────────────────────────────────────────────────
// These must be last to avoid conflicting with admin/checkin routes

// Long URL (legacy): /INV-XXXXXX/slug
Route::get('/{publicId}/{slug}', [InvitationController::class, 'show'])
    ->name('invitation.show')
    ->where('publicId', 'INV-[A-Z0-9]{6}');

// Short URL: /xxxxxx/nama (6 lowercase alphanumeric)
Route::get('/{shortId}/{slug}', [InvitationController::class, 'showShort'])
    ->name('invitation.short')
    ->where('shortId', '[a-z0-9]{6}');

// Personalized routes — rate limited to prevent token enumeration
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/{publicId}/{slug}/u/{token}', [InvitationController::class, 'showPersonalized'])
        ->name('invitation.personalized')
        ->where('publicId', 'INV-[A-Z0-9]{6}');

    Route::get('/{shortId}/{slug}/u/{token}', [InvitationController::class, 'showShortPersonalized'])
        ->name('invitation.short.personalized')
        ->where('shortId', '[a-z0-9]{6}')
        ->where('token', '[a-zA-Z0-9]{12,64}');
});

// RSVP & Guest Book (public, rate-limited)
Route::middleware('throttle:30,1')->group(function () {
    // Long URL RSVP
    Route::post('/{publicId}/{slug}/u/{token}/rsvp', [RsvpController::class, 'store'])
        ->name('rsvp.store')
        ->where('publicId', 'INV-[A-Z0-9]{6}');

    // Short URL RSVP
    Route::post('/{shortId}/{slug}/u/{token}/rsvp', [RsvpController::class, 'storeShort'])
        ->name('rsvp.store.short')
        ->where('shortId', '[a-z0-9]{6}')
        ->where('token', '[a-zA-Z0-9]{12,64}');

    // QR code (public, per token)
    Route::get('/{anyId}/{slug}/u/{token}/qr', [RsvpController::class, 'qr'])
        ->name('rsvp.qr')
        ->where('token', '[a-zA-Z0-9]{12,64}');

    // Guest Book (both URL formats use same route via publicId OR shortId)
    Route::post('/{id}/{slug}/guestbook', [GuestBookController::class, 'store'])
        ->name('guestbook.store');
});
