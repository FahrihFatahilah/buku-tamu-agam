@extends('admin.layout')

@section('title', 'Musik — ' . $wedding->coupleName())

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <p class="text-xs text-stone-400 mb-0.5">
            <a href="{{ route('admin.weddings.edit', $wedding) }}" class="hover:text-stone-600">{{ $wedding->coupleName() }}</a>
            <span class="mx-1">›</span> Musik
        </p>
        <h1 class="text-xl font-semibold text-stone-800">Musik & Playlist</h1>
    </div>

    {{-- Settings --}}
    <div class="bg-white border border-stone-200 p-5 mb-6">
        <h2 class="text-sm font-medium text-stone-700 mb-4">Pengaturan</h2>
        <form method="POST" action="{{ route('admin.weddings.playlist.settings', $wedding) }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <label class="flex items-center gap-2 text-sm text-stone-600 cursor-pointer">
                    <input type="hidden" name="autoplay" value="0">
                    <input type="checkbox" name="autoplay" value="1" {{ $playlist->autoplay ? 'checked' : '' }}
                        class="w-4 h-4 border-stone-300">
                    Autoplay
                </label>
                <label class="flex items-center gap-2 text-sm text-stone-600 cursor-pointer">
                    <input type="hidden" name="loop" value="0">
                    <input type="checkbox" name="loop" value="1" {{ $playlist->loop ? 'checked' : '' }}
                        class="w-4 h-4 border-stone-300">
                    Loop
                </label>
                <label class="flex items-center gap-2 text-sm text-stone-600 cursor-pointer">
                    <input type="hidden" name="shuffle" value="0">
                    <input type="checkbox" name="shuffle" value="1" {{ $playlist->shuffle ? 'checked' : '' }}
                        class="w-4 h-4 border-stone-300">
                    Shuffle
                </label>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Volume ({{ $playlist->volume }}%)</label>
                    <input type="range" name="volume" min="0" max="100" value="{{ $playlist->volume }}"
                        class="w-full">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    {{-- Upload Track --}}
    <div class="bg-white border border-stone-200 p-5 mb-6">
        <h2 class="text-sm font-medium text-stone-700 mb-4">Tambah Lagu</h2>
        <form method="POST" action="{{ route('admin.weddings.playlist.tracks.store', $wedding) }}"
            enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Judul</label>
                    <input type="text" name="title" placeholder="Judul lagu"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Artis</label>
                    <input type="text" name="artist" placeholder="Nama artis"
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">File Audio (MP3/OGG/WAV, maks 20MB)</label>
                <input type="file" name="file" accept="audio/*" required
                    class="text-sm text-stone-600 file:mr-2 file:px-3 file:py-1.5 file:border file:border-stone-200 file:text-xs file:bg-stone-50 file:text-stone-600 hover:file:bg-stone-100">
            </div>
            @error('file')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
                    Upload
                </button>
            </div>
        </form>
    </div>

    {{-- Track List --}}
    <div class="bg-white border border-stone-200">
        @if($playlist->items->isEmpty())
        <div class="px-6 py-10 text-center text-stone-400 text-sm">Belum ada lagu.</div>
        @else
        <div class="divide-y divide-stone-100">
            @foreach($playlist->items as $item)
            <div class="flex items-center gap-3 px-4 py-3">
                <span class="text-stone-300 text-xs w-5 text-center">{{ $loop->iteration }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-stone-800 truncate">{{ $item->title }}</p>
                    @if($item->artist)
                    <p class="text-xs text-stone-400">{{ $item->artist }}</p>
                    @endif
                </div>
                <audio controls class="h-8 w-40 hidden sm:block" preload="none">
                    <source src="{{ Storage::url($item->file_path) }}">
                </audio>
                <form method="POST" action="{{ route('admin.weddings.playlist.tracks.destroy', [$wedding, $item]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors"
                        onclick="return confirm('Hapus lagu ini?')">Hapus</button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
