@php
    $p = $node['props'] ?? [];
    $style = $p['style'] ?? 'solid';
    $style = in_array($style, ['solid', 'dashed', 'dotted', 'double'], true) ? $style : 'solid';
@endphp
<hr style="border: 0; border-top-style: {{ $style }}; border-top-width: inherit; border-top-color: inherit; margin: 0 auto;">
