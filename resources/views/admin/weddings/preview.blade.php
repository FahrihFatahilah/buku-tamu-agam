@extends('admin.layout')

@section('title', 'Preview — ' . $wedding->coupleName())

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <p class="text-xs text-stone-400 mb-0.5">
            <a href="{{ route('admin.weddings.edit', $wedding) }}" class="hover:text-stone-600">{{ $wedding->coupleName() }}</a>
            <span class="mx-1">›</span> Preview
        </p>
        <h1 class="text-xl font-semibold text-stone-800">Preview Undangan</h1>
    </div>
    <div class="flex items-center gap-1" x-data="{ device: 'mobile' }">
        <button @click="device = 'mobile'"
            :class="device === 'mobile' ? 'bg-stone-800 text-white' : 'text-stone-500 hover:text-stone-700'"
            class="px-3 py-1.5 text-xs transition-colors">
            Mobile
        </button>
        <button @click="device = 'tablet'"
            :class="device === 'tablet' ? 'bg-stone-800 text-white' : 'text-stone-500 hover:text-stone-700'"
            class="px-3 py-1.5 text-xs transition-colors">
            Tablet
        </button>
        <button @click="device = 'desktop'"
            :class="device === 'desktop' ? 'bg-stone-800 text-white' : 'text-stone-500 hover:text-stone-700'"
            class="px-3 py-1.5 text-xs transition-colors">
            Desktop
        </button>

        <div class="ml-4 flex items-center gap-2" x-data="{ device: 'mobile' }">
            {{-- device switcher already above, this is the iframe container --}}
        </div>
    </div>
</div>

<div x-data="{ device: 'mobile' }" class="flex flex-col items-center">
    {{-- Device switcher --}}
    <div class="flex items-center gap-1 mb-4">
        <button @click="device = 'mobile'"
            :class="device === 'mobile' ? 'bg-stone-800 text-white' : 'bg-white border border-stone-200 text-stone-500 hover:border-stone-300'"
            class="px-3 py-1.5 text-xs transition-colors">
            📱 Mobile
        </button>
        <button @click="device = 'tablet'"
            :class="device === 'tablet' ? 'bg-stone-800 text-white' : 'bg-white border border-stone-200 text-stone-500 hover:border-stone-300'"
            class="px-3 py-1.5 text-xs transition-colors">
            📟 Tablet
        </button>
        <button @click="device = 'desktop'"
            :class="device === 'desktop' ? 'bg-stone-800 text-white' : 'bg-white border border-stone-200 text-stone-500 hover:border-stone-300'"
            class="px-3 py-1.5 text-xs transition-colors">
            🖥 Desktop
        </button>
    </div>

    {{-- Frame --}}
    <div class="bg-stone-100 p-4 border border-stone-200 transition-all duration-300"
        :style="device === 'mobile' ? 'width: 390px' : device === 'tablet' ? 'width: 768px' : 'width: 100%'">
        <div class="bg-stone-300 h-6 flex items-center px-3 gap-1.5 mb-0">
            <span class="w-2.5 h-2.5 rounded-full bg-stone-400"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-stone-400"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-stone-400"></span>
            <span class="flex-1 mx-2 bg-white h-3 text-xs text-stone-400 px-2 flex items-center font-mono text-[10px] truncate">
                {{ $wedding->publicUrl() }}
            </span>
        </div>
        <iframe
            src="{{ $wedding->publicUrl() }}"
            class="w-full border-0 bg-white"
            :style="device === 'mobile' ? 'height: 844px' : device === 'tablet' ? 'height: 1024px' : 'height: 900px'"
            title="Preview {{ $wedding->coupleName() }}">
        </iframe>
    </div>

    <p class="mt-3 text-xs text-stone-400">
        Preview menggunakan URL publik. Pastikan undangan sudah di-publish atau gunakan
        <a href="{{ route('admin.weddings.preview', $wedding) }}" class="underline hover:text-stone-600">mode preview draft</a>.
    </p>
</div>
@endsection
