@extends('admin.layout')

@section('title', 'Undangan')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-medium text-stone-800">Undangan</h1>
        <p class="text-sm text-stone-400 mt-0.5">{{ $weddings->total() }} undangan</p>
    </div>
    @can('create', \App\Models\Wedding::class)
    <a href="{{ route('admin.weddings.create') }}" class="inline-flex items-center px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
        Buat Undangan
    </a>
    @endcan
</div>

<div class="bg-white border border-stone-200">
    @if($weddings->isEmpty())
    <div class="px-6 py-12 text-center">
        <p class="text-stone-400 text-sm">Belum ada undangan.</p>
        @can('create', \App\Models\Wedding::class)
        <a href="{{ route('admin.weddings.create') }}" class="mt-3 inline-block text-sm text-stone-600 underline underline-offset-2">
            Buat undangan pertama
        </a>
        @endcan
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-stone-200">
                <th class="text-left px-5 py-3 text-xs font-medium text-stone-400 uppercase tracking-wider">Undangan</th>
                <th class="text-left px-5 py-3 text-xs font-medium text-stone-400 uppercase tracking-wider hidden md:table-cell">Tanggal</th>
                <th class="text-left px-5 py-3 text-xs font-medium text-stone-400 uppercase tracking-wider hidden lg:table-cell">Template</th>
                <th class="text-left px-5 py-3 text-xs font-medium text-stone-400 uppercase tracking-wider">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @foreach($weddings as $wedding)
            <tr class="hover:bg-stone-50 transition-colors">
                <td class="px-5 py-4">
                    <div>
                        <p class="font-medium text-stone-800">{{ $wedding->coupleName() }}</p>
                        <p class="text-xs text-stone-400 mt-0.5 font-mono">{{ $wedding->public_id }}</p>
                    </div>
                </td>
                <td class="px-5 py-4 text-stone-500 hidden md:table-cell">
                    {{ $wedding->date?->format('d M Y') ?? '—' }}
                </td>
                <td class="px-5 py-4 text-stone-500 hidden lg:table-cell">
                    {{ $wedding->template?->name ?? '—' }}
                </td>
                <td class="px-5 py-4">
                    @if($wedding->status === 'published')
                        <span class="inline-flex items-center gap-1.5 text-xs text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            Aktif
                        </span>
                    @elseif($wedding->status === 'draft')
                        <span class="inline-flex items-center gap-1.5 text-xs text-stone-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-stone-300"></span>
                            Draft
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs text-stone-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-stone-200"></span>
                            Arsip
                        </span>
                    @endif
                </td>
                <td class="px-5 py-4 text-right">
                    <a href="{{ route('admin.weddings.edit', $wedding) }}" class="text-xs text-stone-500 hover:text-stone-800 transition-colors">
                        Kelola
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($weddings->hasPages())
    <div class="px-5 py-4 border-t border-stone-100">
        {{ $weddings->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
