@extends('admin.layout')

@section('title', 'Buat Undangan')

@section('content')
<div class="max-w-xl">
    <div class="mb-6">
        <a href="{{ route('admin.weddings.index') }}" class="text-sm text-stone-400 hover:text-stone-600 transition-colors">← Kembali</a>
        <h1 class="text-xl font-medium text-stone-800 mt-2">Buat Undangan Baru</h1>
    </div>

    <form method="POST" action="{{ route('admin.weddings.store') }}" class="space-y-5">
        @csrf

        <div class="bg-white border border-stone-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-stone-700 pb-3 border-b border-stone-100">Informasi Pengantin</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Nama Pengantin Pria <span class="text-red-400">*</span></label>
                    <input type="text" name="groom_name" value="{{ old('groom_name') }}" required
                        class="w-full px-3 py-2 border border-stone-300 text-sm text-stone-800 focus:outline-none focus:border-stone-500 transition-colors @error('groom_name') border-red-400 @enderror">
                    @error('groom_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Nama Pengantin Wanita <span class="text-red-400">*</span></label>
                    <input type="text" name="bride_name" value="{{ old('bride_name') }}" required
                        class="w-full px-3 py-2 border border-stone-300 text-sm text-stone-800 focus:outline-none focus:border-stone-500 transition-colors @error('bride_name') border-red-400 @enderror">
                    @error('bride_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm text-stone-600 mb-1.5">Tanggal Pernikahan</label>
                <input type="date" name="date" value="{{ old('date') }}"
                    class="w-full px-3 py-2 border border-stone-300 text-sm text-stone-800 focus:outline-none focus:border-stone-500 transition-colors">
            </div>

            <div>
                <label class="block text-sm text-stone-600 mb-1.5">Nama Venue</label>
                <input type="text" name="venue" value="{{ old('venue') }}"
                    class="w-full px-3 py-2 border border-stone-300 text-sm text-stone-800 focus:outline-none focus:border-stone-500 transition-colors">
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.weddings.index') }}" class="px-4 py-2 text-sm text-stone-500 hover:text-stone-700 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
                Buat Undangan
            </button>
        </div>
    </form>
</div>
@endsection
