<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Structural validation for a builder document payload.
 *
 * Deep sanitisation happens in App\Builder\DocumentValidator; this request only
 * guards the envelope so a malformed payload is rejected early.
 */
class UpdateBuilderDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('buildDocument', $this->route('wedding')) ?? false;
    }

    public function rules(): array
    {
        return [
            'version' => 'nullable|integer|min:1|max:99',
            'theme' => 'nullable|array',
            'nodes' => 'nullable|array|max:500',
            'nodes.*' => 'array',
            'nodes.*.id' => 'nullable|string|max:64',
            'nodes.*.type' => 'required|string|max:40',
            'nodes.*.props' => 'nullable|array',
            'nodes.*.styles' => 'nullable|array',
            'nodes.*.children' => 'nullable|array',
            'nodes.*.animation' => 'nullable|array',
            'nodes.*.effects' => 'nullable|array',
            'overlays' => 'nullable|array|max:20',
            'overlays.*.id' => 'nullable|string|max:64',
            'overlays.*.type' => 'required|string|max:40',
            'overlays.*.enabled' => 'nullable|boolean',
            'overlays.*.props' => 'nullable|array',
            'overlays.*.styles' => 'nullable|array',
        ];
    }
}
