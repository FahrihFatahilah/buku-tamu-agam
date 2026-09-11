@php
    $p = $node['props'] ?? [];
    $html = is_string($p['html'] ?? null) ? $p['html'] : '';
@endphp
{{--
    Rich text is sanitised to a small allowlist of formatting tags. Author
    input can never introduce scripts, styles, event handlers or links to
    non-safe schemes.
--}}
@php
    $clean = strip_tags($html, '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><a>');
    $clean = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
    $clean = preg_replace('/javascript\s*:/i', '', $clean);
    $clean = preg_replace('/<(a)\s+(?![^>]*\brel=)/i', '<$1 rel="noopener noreferrer" ', $clean);
@endphp
<div class="n-richtext">{!! $clean !!}</div>
