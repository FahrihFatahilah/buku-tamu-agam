<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageGuests', $this->route('wedding'));
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:100',
            'category_id' => 'nullable|exists:guest_categories,id',
            'max_pax'     => 'required|integer|min:1|max:20',
            'notes'       => 'nullable|string|max:500',
        ];
    }
}
