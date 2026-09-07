{{--
    Section background + overlay renderer.
    Usage: @include('templates._section-bg', ['section' => $sectionModel, 'defaultBg' => '#1a0a0a'])

    settings keys:
      bg_color       — hex color, e.g. "#1a0a0a"
      bg_image       — storage path
      bg_opacity     — 0–100 (default 100)
      bg_fixed       — bool, parallax fixed attachment
      overlay_color  — hex, e.g. "#000000"
      overlay_opacity — 0–100 (default 40)
      overlay_image  — storage path
      overlay_img_opacity — 0–100 (default 15)
      overlay_img_position — top-left|top-right|bottom-left|bottom-right|center|full
--}}
@php
    $s          = $section?->settings ?? [];
    $bgColor    = $s['bg_color']    ?? $defaultBg ?? 'transparent';
    $bgImage    = $s['bg_image']    ?? null;
    $bgOpacity  = isset($s['bg_opacity'])  ? (int)$s['bg_opacity'] / 100  : 1;
    $bgFixed    = !empty($s['bg_fixed']);

    $overlayColor   = $s['overlay_color']   ?? '#000000';
    $overlayOpacity = isset($s['overlay_opacity']) ? (int)$s['overlay_opacity'] / 100 : 0;

    $overlayImg     = $s['overlay_image']       ?? null;
    $overlayImgOp   = isset($s['overlay_img_opacity']) ? (int)$s['overlay_img_opacity'] / 100 : 0.15;
    $overlayImgPos  = $s['overlay_img_position'] ?? 'bottom-right';

    $posClasses = match($overlayImgPos) {
        'top-left'     => 'top-0 left-0',
        'top-right'    => 'top-0 right-0',
        'bottom-left'  => 'bottom-0 left-0',
        'center'       => 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2',
        'full'         => 'inset-0 w-full h-full object-cover',
        default        => 'bottom-0 right-0',
    };
@endphp

{{-- Solid/image background layer --}}
<div class="section-bg-layer"
    style="background-color: {{ $bgColor }};
           {{ $bgImage ? 'background-image: url(' . Storage::url($bgImage) . ');' : '' }}
           {{ $bgFixed ? 'background-attachment: fixed;' : '' }}
           opacity: {{ $bgOpacity }};">
</div>

{{-- Color overlay --}}
@if($overlayOpacity > 0)
<div class="section-overlay"
    style="background-color: {{ $overlayColor }}; opacity: {{ $overlayOpacity }};"></div>
@endif

{{-- Image overlay --}}
@if($overlayImg && $overlayImgOp > 0)
<img src="{{ Storage::url($overlayImg) }}"
    alt=""
    aria-hidden="true"
    class="section-overlay-img {{ $overlayImgPos === 'full' ? 'inset-0 w-full h-full object-cover' : 'absolute ' . $posClasses . ' w-64 h-64 object-contain' }}"
    style="opacity: {{ $overlayImgOp }};">
@endif
