{{--
    Renders a list of inspector field definitions using the matching control
    partial. Unknown field types fall back to a text input, so a new field type
    added to the registry degrades gracefully until its control exists.
--}}
@foreach($fields as $field)
    @php
        $view = 'builder.inspector.fields.' . ($field['type'] ?? 'text');

        if (! view()->exists($view)) {
            $view = 'builder.inspector.fields.text';
        }
    @endphp

    <div @if(!empty($field['responsive'])) data-responsive-field @endif>
        @include($view, [
            'field' => $field,
            'target' => $target,
            // Some controls (bank account) need live wedding data.
            'giftMethods' => $giftMethods ?? collect(),
        ])
    </div>
@endforeach
