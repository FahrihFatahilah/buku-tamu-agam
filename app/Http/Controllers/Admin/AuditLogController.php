<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $logs = AuditLog::with(['user', 'wedding'])
            ->when($request->action, fn($q) => $q->where('action', 'like', "%{$request->action}%"))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->wedding_id, fn($q) => $q->where('wedding_id', $request->wedding_id))
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.audit.index', compact('logs'));
    }
}
