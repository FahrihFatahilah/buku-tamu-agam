@php
    $p = $overlay['props'] ?? [];
    $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($overlay['id'] ?? 'ov'));
    $from = $p['from'] ?? '#000000';
    $to = $p['to'] ?? '#00000000';
    $dir = $p['direction'] ?? 'to-top';
    $dir = in_array($dir, ['to-top', 'to-bottom', 'to-left', 'to-right'], true) ? $dir : 'to-top';

    $safe = fn ($c) => preg_match('/^#([0-9a-fA-F]{3,8})$/', (string) $c) ? $c : 'transparent';
@endphp
<div data-overlay-id="{{ $id }}" class="n-overlay" aria-hidden="true"
    style="background: linear-gradient({{ $dir }}, {{ $safe($from) }}, {{ $safe($to) }});"></div>
