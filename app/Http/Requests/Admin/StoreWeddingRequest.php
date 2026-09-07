<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeddingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Wedding::class);
    }

    public function rules(): array
    {
        return [
            'groom_name' => 'required|string|max:100',
            'bride_name' => 'required|string|max:100',
            'title'      => 'nullable|string|max:200',
            'date'       => 'nullable|date',
            'venue'      => 'nullable|string|max:200',
        ];
    }
}
