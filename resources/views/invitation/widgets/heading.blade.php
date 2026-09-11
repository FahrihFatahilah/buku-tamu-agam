@php
    $p = $node['props'] ?? [];
    $level = (int) ($p['level'] ?? 2);
    $level = in_array($level, [1, 2, 3], true) ? $level : 2;
@endphp
<h{{ $level }} class="n-display" data-edit-prop="text">{{ $p['text'] ?? '' }}</h{{ $level }}>
