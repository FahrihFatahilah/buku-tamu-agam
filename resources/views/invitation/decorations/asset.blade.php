@php
    /** @var \App\Builder\PageRenderer $pageRenderer */
    $pageRenderer = app(\App\Builder\PageRenderer::class);

    $p = $node['props'] ?? [];
    $asset = is_string($p['asset'] ?? null) ? $p['asset'] : '';
    $alt = (string) ($p['alt'] ?? '');

    // Built-in decorations are inlined so they inherit the theme colour.
    $builtin = null;
    $inlineSvg = null;

    if (str_starts_with($asset, 'builtin:')) {
        $name = substr($asset, 8);

        if (preg_match('/^[a-z0-9-]{1,40}$/', $name)) {
            $builtin = $name;
            $path = public_path("builder/decorations/{$name}.svg");

            if (is_file($path)) {
                // Our own asset, but strip anything executable defensively.
                $svg = file_get_contents($path);
                $svg = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $svg);
                $svg = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $svg);
                $inlineSvg = $svg;
            }
        }
    }

    $uploadedUrl = $builtin ? null : $pageRenderer->decorationUrl($node);
@endphp

@if($inlineSvg)
    @php
        // Colour: explicit prop, else the theme accent.
        $color = is_string($p['color'] ?? null) && preg_match('/^#([0-9a-fA-F]{3,8})$/', $p['color'])
            ? $p['color']
            : null;
    @endphp
    <span class="n-deco-svg block w-full h-full"
        @if($color) style="color: {{ $color }};" @else style="color: var(--n-accent, #B8960C);" @endif
        @if($alt === '') aria-hidden="true" @endif>{!! $inlineSvg !!}</span>
@elseif($uploadedUrl)
    <img src="{{ $uploadedUrl }}" alt="{{ $alt }}" loading="lazy" decoding="async"
        @if($alt === '') aria-hidden="true" @endif>
@endif
