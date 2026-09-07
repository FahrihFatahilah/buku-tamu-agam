@php $closingSection = $sections->firstWhere('section_key', 'closing'); @endphp

{{-- Section ini hanya spacer/trigger untuk curtain closing di layout --}}
<section id="closing" class="section-bg relative min-h-[40vh]">
    @include('templates._section-bg', ['section' => $closingSection, 'defaultBg' => '#1a0a0a'])
    {{-- Konten ditampilkan oleh curtain-closing overlay di layout --}}
</section>
