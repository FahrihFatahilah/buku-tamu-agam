@extends('admin.layout')

@section('title', 'Check-in')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-stone-800">Check-in</h1>
        <p class="text-sm text-stone-400 mt-0.5">Pilih undangan untuk memulai check-in.</p>
    </div>

    @if($weddings->isEmpty())
    <div class="bg-white border border-stone-200 px-6 py-12 text-center text-stone-400 text-sm">
        Tidak ada undangan aktif.
    </div>
    @else
    <div class="space-y-2">
        @foreach($weddings as $wedding)
        <a href="{{ route('checkin.scanner', $wedding) }}"
            class="flex items-center justify-between bg-white border border-stone-200 px-5 py-4 hover:border-stone-300 transition-colors group">
            <div>
                <p class="font-medium text-stone-800">{{ $wedding->coupleName() }}</p>
                <p class="text-xs text-stone-400 mt-0.5 font-mono">{{ $wedding->public_id }}</p>
                @if($wedding->date)
                <p class="text-xs text-stone-400">{{ $wedding->date->format('d M Y') }}</p>
                @endif
            </div>
            <span class="text-stone-300 group-hover:text-stone-500 transition-colors">→</span>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
