@extends('admin.layout')

@section('title', 'Template')

@section('content')
<div class="max-w-4xl">
    <div class="flex items-start justify-between mb-6">
        <div>
            <p class="text-xs text-stone-400 mb-0.5">Platform</p>
            <h1 class="text-xl font-semibold text-stone-800">Template</h1>
            <p class="text-sm text-stone-400 mt-1">
                Template menentukan urutan section, palet warna, dan tipografi undangan.
            </p>
        </div>
        <a href="{{ route('admin.templates.create') }}"
            class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors shrink-0">
            + Tambah Template
        </a>
    </div>

    @if($templates->isEmpty())
    <div class="bg-white border border-stone-200 px-6 py-12 text-center text-stone-400 text-sm">
        Belum ada template.
    </div>
    @else
    <div class="bg-white border border-stone-200 divide-y divide-stone-100">
        @foreach($templates as $template)
        @php
            $palette = $template->default_settings['palette'] ?? [];
            $font = $template->default_settings['fonts']['display'] ?? null;
            $sectionCount = count($template->default_sections ?? []);
        @endphp
        <div class="px-5 py-4 flex items-center gap-4 hover:bg-stone-50 transition-colors">
            {{-- Palette swatch --}}
            <div class="flex -space-x-1 shrink-0">
                @foreach(['primary', 'secondary', 'accent'] as $slot)
                <span class="w-5 h-5 rounded-full border border-white"
                    style="background: {{ $palette[$slot] ?? '#e5e5e5' }}"></span>
                @endforeach
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <p class="font-medium text-stone-800 truncate">{{ $template->name }}</p>
                    @if(!$template->is_active)
                    <span class="text-xs text-stone-400 bg-stone-100 px-2 py-0.5">Nonaktif</span>
                    @else
                    <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5">Aktif</span>
                    @endif
                </div>
                <p class="text-xs text-stone-400 mt-0.5">
                    <span class="font-mono">{{ $template->key }}</span>
                    <span class="mx-1 text-stone-300">·</span>{{ $sectionCount }} section
                    <span class="mx-1 text-stone-300">·</span>{{ $template->category }}
                    @if($font)
                    <span class="mx-1 text-stone-300">·</span>{{ $font }}
                    @endif
                </p>
            </div>

            <div class="text-right shrink-0">
                <p class="text-xs text-stone-400">{{ $template->weddings_count }} undangan</p>
            </div>

            <a href="{{ route('admin.templates.edit', $template) }}"
                class="text-xs text-stone-500 hover:text-stone-800 transition-colors px-2 py-1 border border-stone-200 hover:border-stone-400 shrink-0">
                Atur
            </a>
        </div>
        @endforeach
    </div>
    @endif

    <p class="text-xs text-stone-400 mt-4">
        Section yang punya view khusus akan memakai desain template tersebut. Section tanpa view khusus
        memakai desain default, dengan warna dan font dari palet template ini.
    </p>
</div>
@endsection
