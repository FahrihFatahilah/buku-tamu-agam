@extends('admin.layout')

@section('title', 'Media — ' . $wedding->coupleName())

@section('content')
<div class="max-w-4xl">
    <div class="mb-6">
        <p class="text-xs text-stone-400 mb-0.5">
            <a href="{{ route('admin.weddings.edit', $wedding) }}" class="hover:text-stone-600">{{ $wedding->coupleName() }}</a>
            <span class="mx-1">›</span> Media
        </p>
        <h1 class="text-xl font-semibold text-stone-800">Media</h1>
    </div>

    {{-- Upload Form --}}
    <div class="bg-white border border-stone-200 p-5 mb-6">
        <h2 class="text-sm font-medium text-stone-700 mb-4">Upload Media</h2>
        <form method="POST" action="{{ route('admin.weddings.media.store', $wedding) }}" enctype="multipart/form-data"
            class="flex flex-wrap gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs text-stone-500 mb-1">Koleksi</label>
                <select name="collection" class="border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                    <option value="hero">Hero</option>
                    <option value="couple">Couple</option>
                    <option value="gallery">Gallery</option>
                    <option value="family">Keluarga</option>
                    <option value="prewedding">Prewedding</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">File</label>
                <input type="file" name="file" accept="image/*" required
                    class="text-sm text-stone-600 file:mr-2 file:px-3 file:py-1.5 file:border file:border-stone-200 file:text-xs file:bg-stone-50 file:text-stone-600 hover:file:bg-stone-100">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Alt Text</label>
                <input type="text" name="alt_text" placeholder="Deskripsi gambar"
                    class="border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400 w-48">
            </div>
            <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
                Upload
            </button>
        </form>
        @error('file')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    {{-- Media by collection --}}
    @forelse($media as $collection => $items)
    <div class="mb-8">
        <h2 class="text-sm font-medium text-stone-600 mb-3 uppercase tracking-wide">{{ ucfirst($collection) }}</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            @foreach($items as $item)
            <div class="group relative bg-stone-100 aspect-square overflow-hidden">
                <img src="{{ Storage::url($item->file_path) }}" alt="{{ $item->alt_text }}"
                    class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                    <form method="POST" action="{{ route('admin.weddings.media.destroy', [$wedding, $item]) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-white text-xs bg-red-500/80 px-2 py-1 hover:bg-red-600 transition-colors"
                            onclick="return confirm('Hapus media ini?')">Hapus</button>
                    </form>
                </div>
                @if($item->alt_text)
                <div class="absolute bottom-0 left-0 right-0 bg-black/50 px-2 py-1">
                    <p class="text-white text-xs truncate">{{ $item->alt_text }}</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="bg-white border border-stone-200 px-6 py-12 text-center text-stone-400 text-sm">
        Belum ada media. Upload foto untuk undangan.
    </div>
    @endforelse
</div>
@endsection
