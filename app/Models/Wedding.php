<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Wedding extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'template_id', 'public_id', 'short_id', 'slug', 'title',
        'groom_name', 'bride_name', 'groom_nickname', 'bride_nickname',
        'groom_father', 'groom_mother', 'bride_father', 'bride_mother',
        'description', 'quote', 'date', 'venue', 'address',
        'latitude', 'longitude', 'maps_url', 'status', 'published_at',
        'seo_title', 'seo_description', 'og_image', 'favicon',
        'appearance', 'animation_config', 'settings',
    ];

    protected $casts = [
        'date' => 'date',
        'published_at' => 'datetime',
        'appearance' => 'array',
        'animation_config' => 'array',
        'settings' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function domains()
    {
        return $this->hasMany(WeddingDomain::class);
    }

    public function primaryDomain()
    {
        return $this->hasOne(WeddingDomain::class)->where('is_primary', true)->where('is_active', true);
    }

    public function sections()
    {
        return $this->hasMany(WeddingSection::class)->orderBy('sort_order');
    }

    public function events()
    {
        return $this->hasMany(WeddingEvent::class)->orderBy('sort_order');
    }

    public function guestCategories()
    {
        return $this->hasMany(GuestCategory::class)->orderBy('sort_order');
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function guestBookEntries()
    {
        return $this->hasMany(GuestBookEntry::class);
    }

    public function giftMethods()
    {
        return $this->hasMany(GiftMethod::class)->orderBy('sort_order');
    }

    public function media()
    {
        return $this->hasMany(WeddingMedia::class);
    }

    public function playlists()
    {
        return $this->hasMany(Playlist::class);
    }

    public function activePlaylist()
    {
        return $this->hasOne(Playlist::class)->where('is_active', true);
    }

    public function visibilityRules()
    {
        return $this->hasMany(VisibilityRule::class);
    }

    public function slugHistory()
    {
        return $this->hasMany(WeddingSlugHistory::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function coupleName(): string
    {
        return "{$this->bride_name} & {$this->groom_name}";
    }

    public function publicUrl(): string
    {
        return url("/{$this->public_id}/{$this->slug}");
    }

    /**
     * Short URL: /{short_id}/{name}
     * e.g. /a3f9b2/andi-sari
     */
    public function shortUrl(): string
    {
        $id = $this->short_id ?? $this->public_id;
        return url("/{$id}/{$this->slug}");
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    protected static function booted(): void
    {
        static::creating(function (Wedding $wedding) {
            if (empty($wedding->public_id)) {
                $wedding->public_id = static::generatePublicId();
            }
            if (empty($wedding->short_id)) {
                $wedding->short_id = static::generateShortId();
            }
        });

        static::updating(function (Wedding $wedding) {
            if ($wedding->isDirty('slug')) {
                $oldSlug = $wedding->getOriginal('slug');
                if ($oldSlug && $oldSlug !== $wedding->slug) {
                    WeddingSlugHistory::create([
                        'wedding_id' => $wedding->id,
                        'slug' => $oldSlug,
                        'retired_at' => now(),
                    ]);
                }
            }
        });
    }

    public static function generatePublicId(): string
    {
        do {
            $id = 'INV-' . strtoupper(Str::random(6));
        } while (static::where('public_id', $id)->exists());

        return $id;
    }

    public static function generateShortId(): string
    {
        do {
            $id = strtolower(Str::random(6));
        } while (static::where('short_id', $id)->exists());

        return $id;
    }
}
