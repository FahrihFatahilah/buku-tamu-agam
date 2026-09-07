@extends('admin.layout')

@section('title', 'Audit Log')

@section('content')
<div class="max-w-6xl">
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-stone-800">Audit Log</h1>
        <p class="text-sm text-stone-400 mt-0.5">Semua aksi penting tercatat di sini.</p>
    </div>

    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter aksi..."
            class="border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400 w-48">
        <input type="text" name="wedding_id" value="{{ request('wedding_id') }}" placeholder="Wedding ID"
            class="border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400 w-32">
        <button type="submit" class="px-4 py-2 bg-stone-100 text-stone-700 text-sm hover:bg-stone-200 transition-colors">Filter</button>
        @if(request()->hasAny(['action','wedding_id','user_id']))
        <a href="{{ route('admin.audit.index') }}" class="px-4 py-2 text-stone-400 text-sm hover:text-stone-600">Reset</a>
        @endif
    </form>

    <div class="bg-white border border-stone-200">
        @if($logs->isEmpty())
        <div class="px-6 py-12 text-center text-stone-400 text-sm">Belum ada log.</div>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-200 bg-stone-50">
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Waktu</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Aksi</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide hidden md:table-cell">User</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide hidden lg:table-cell">Entity</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide hidden lg:table-cell">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($logs as $log)
                <tr class="hover:bg-stone-50 transition-colors">
                    <td class="px-4 py-3 text-xs text-stone-400 whitespace-nowrap">
                        {{ $log->created_at->format('d/m H:i') }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-mono text-xs text-stone-700">{{ $log->action }}</span>
                    </td>
                    <td class="px-4 py-3 text-stone-500 hidden md:table-cell">
                        {{ $log->user?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-stone-400 text-xs hidden lg:table-cell">
                        {{ $log->entity_type }}:{{ $log->entity_id }}
                    </td>
                    <td class="px-4 py-3 text-stone-400 text-xs hidden lg:table-cell font-mono">
                        {{ $log->ip_address }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-stone-100">{{ $logs->withQueryString()->links() }}</div>
        @endif
        @endif
    </div>
</div>
@endsection
