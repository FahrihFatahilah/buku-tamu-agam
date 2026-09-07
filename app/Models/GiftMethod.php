<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftMethod extends Model
{
    protected $fillable = [
        'wedding_id', 'type', 'label', 'bank_name', 'account_number',
        'account_holder', 'merchant_name', 'image', 'description',
        'is_active', 'sort_order',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
