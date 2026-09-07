<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestCategory extends Model
{
    use HasFactory;

    protected $fillable = ['wedding_id', 'name', 'color', 'description', 'sort_order'];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class, 'category_id');
    }
}
