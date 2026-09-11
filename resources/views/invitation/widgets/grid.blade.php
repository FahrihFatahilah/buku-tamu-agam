@php
    $p = $node['props'] ?? [];
    $columns = (int) ($p['columns'] ?? 2);
    $columns = max(1, min(4, $columns));

    // Responsive column count is a CSS concern, not markup duplication.
    $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($node['id'] ?? ''));
@endphp
<div class="n-grid" data-n-grid="{{ $columns }}" style="display: grid; grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr));">
    {!! $childrenHtml !!}
</div>
