@extends('admin.layout')

@section('title', 'Buku Tamu — ' . $wedding->coupleName())

@section('content')
<div class="max-w-4xl">
    <div class="mb-6">
        <p class="text-xs text-stone-400 mb-0.5">
            <a href="{{ route('admin.weddings.edit', $wedding) }}" class="hover:text-stone-600">{{ $wedding->coupleName() }}</a>
            <span class="mx-1">›</span> Buku Tamu
        </p>
        <h1 class="text-xl font-semibold text-stone-800">Buku Tamu</h1>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..."
            class="border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400 w-48">
        <select name="status" class="border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            <option value="">Semua Status</option>
            <option value="pending" @selected(request('status')==='pending')>Pending</option>
            <option value="approved" @selected(request('status')==='approved')>Approved</option>
            <option value="rejected" @selected(request('status')==='rejected')>Rejected</option>
            <option value="hidden" @selected(request('status')==='hidden')>Hidden</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-stone-100 text-stone-700 text-sm hover:bg-stone-200 transition-colors">Filter</button>
        @if(request('search') || request('status'))
        <a href="{{ route('admin.weddings.guestbook.index', $wedding) }}" class="px-4 py-2 text-stone-400 text-sm hover:text-stone-600">Reset</a>
        @endif
    </form>

    {{-- Bulk actions --}}
    <form id="bulk-form" method="POST" action="{{ route('admin.weddings.guestbook.bulk-moderate', $wedding) }}">
        @csrf
        <div class="flex items-center gap-2 mb-3">
            <select name="status" class="border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                <option value="approved">Approve</option>
                <option value="rejected">Reject</option>
                <option value="hidden">Hide</option>
            </select>
            <button type="submit" class="px-3 py-2 bg-stone-100 text-stone-700 text-sm hover:bg-stone-200 transition-colors"
                onclick="return document.querySelectorAll('input[name=\'ids[]\']:checked').length > 0 || (alert('Pilih pesan terlebih dahulu'), false)">
                Terapkan ke Terpilih
            </button>
        </div>

        <div class="bg-white border border-stone-200">
            @if($entries->isEmpty())
            <div class="px-6 py-12 text-center text-stone-400 text-sm">Belum ada pesan.</div>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-stone-200 bg-stone-50">
                        <th class="px-4 py-3 w-8">
                            <input type="checkbox" onchange="document.querySelectorAll('input[name=\'ids[]\']').forEach(c => c.checked = this.checked)"
                                class="w-4 h-4 border-stone-300">
                        </th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Nama</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide hidden md:table-cell">Pesan</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($entries as $entry)
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="ids[]" value="{{ $entry->id }}" class="w-4 h-4 border-stone-300">
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-stone-800">{{ $entry->name }}</p>
                            <p class="text-xs text-stone-400">{{ $entry->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <p class="text-stone-600 text-sm line-clamp-2 max-w-xs">{{ $entry->message }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($entry->status === 'approved')
                            <span class="text-xs text-green-600">Approved</span>
                            @elseif($entry->status === 'pending')
                            <span class="text-xs text-amber-600">Pending</span>
                            @elseif($entry->status === 'rejected')
                            <span class="text-xs text-red-400">Rejected</span>
                            @else
                            <span class="text-xs text-stone-400">Hidden</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1" x-data="{ open: false }">
                                @if($entry->status !== 'approved')
                                <form method="POST" action="{{ route('admin.weddings.guestbook.moderate', [$wedding, $entry]) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="text-xs text-green-600 hover:text-green-700 transition-colors px-1">✓</button>
                                </form>
                                @endif
                                @if($entry->status !== 'rejected')
                                <form method="POST" action="{{ route('admin.weddings.guestbook.moderate', [$wedding, $entry]) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors px-1">✕</button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.weddings.guestbook.destroy', [$wedding, $entry]) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-stone-300 hover:text-stone-500 transition-colors px-1"
                                        onclick="return confirm('Hapus pesan ini?')">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($entries->hasPages())
            <div class="px-4 py-3 border-t border-stone-100">
                {{ $entries->withQueryString()->links() }}
            </div>
            @endif
            @endif
        </div>
    </form>
</div>
@endsection
