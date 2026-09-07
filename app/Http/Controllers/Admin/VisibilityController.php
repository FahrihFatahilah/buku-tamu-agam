<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisibilityRule;
use App\Models\Wedding;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class VisibilityController extends Controller
{
    public function __construct(private AuditLogService $audit) {}

    public function index(Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $wedding->load([
            'guestCategories',
            'giftMethods',
            'events',
            'sections',
            'visibilityRules',
        ]);

        return view('admin.visibility.index', compact('wedding'));
    }

    public function store(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $validated = $request->validate([
            'entity_type' => 'required|in:gift_method,event,section',
            'entity_id'   => 'required|integer',
            'scope'       => 'required|in:wedding_default,category,guest',
            'scope_id'    => 'nullable|integer',
            'is_visible'  => 'required|boolean',
        ]);

        // Upsert — one rule per entity+scope combination
        VisibilityRule::updateOrCreate(
            [
                'wedding_id'  => $wedding->id,
                'entity_type' => $validated['entity_type'],
                'entity_id'   => $validated['entity_id'],
                'scope'       => $validated['scope'],
                'scope_id'    => $validated['scope_id'],
            ],
            ['is_visible' => $validated['is_visible']]
        );

        $this->audit->log('visibility.updated', $validated['entity_type'], $validated['entity_id'], [
            'scope'      => $validated['scope'],
            'is_visible' => $validated['is_visible'],
        ], $wedding->id);

        return back()->with('success', 'Aturan visibilitas disimpan.');
    }

    public function destroy(Wedding $wedding, VisibilityRule $rule)
    {
        $this->authorize('update', $wedding);
        abort_if($rule->wedding_id !== $wedding->id, 403);

        $rule->delete();

        return back()->with('success', 'Aturan visibilitas dihapus.');
    }
}
