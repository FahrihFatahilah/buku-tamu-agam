<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wedding_id', 'category_id', 'name', 'phone', 'email',
        'invitation_token', 'short_token', 'max_pax', 'notes', 'status', 'token_generated_at',
    ];

    protected $casts = [
        'token_generated_at' => 'datetime',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function category()
    {
        return $this->belongsTo(GuestCategory::class, 'category_id');
    }

    public function rsvp()
    {
        return $this->hasOne(Rsvp::class);
    }

    public function checkin()
    {
        return $this->hasOne(GuestCheckin::class);
    }

    /**
     * Short personal URL: /{short_id}/{slug}/u/{short_token}
     * e.g. /demo01/andi-sari/u/a3f9b2c1d4e5
     */
    public function personalUrl(): string
    {
        $wedding = $this->wedding;
        $id      = $wedding->short_id ?? $wedding->public_id;
        $token   = $this->short_token ?? $this->invitation_token;
        return url("/{$id}/{$wedding->slug}/u/{$token}");
    }

    public function isCheckedIn(): bool
    {
        return $this->checkin()->exists();
    }

    /**
     * Full 64-char hex token — stored securely, used for validation.
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Short 12-char base62 token — used in public URL.
     * Still cryptographically random (8 bytes = 2^64 space).
     */
    public static function generateShortToken(): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $bytes = random_bytes(9); // 9 bytes → 12 base62 chars
        $result = '';
        $n = strlen($bytes);
        for ($i = 0; $i < $n; $i++) {
            $result .= $chars[ord($bytes[$i]) % 62];
        }
        // Pad to 12 if needed
        while (strlen($result) < 12) {
            $result .= $chars[random_int(0, 61)];
        }
        return substr($result, 0, 12);
    }

    protected static function booted(): void
    {
        static::creating(function (Guest $guest) {
            if (empty($guest->invitation_token)) {
                $guest->invitation_token  = static::generateToken();
                $guest->token_generated_at = now();
            }
            if (empty($guest->short_token)) {
                // Ensure uniqueness
                do {
                    $short = static::generateShortToken();
                } while (static::where('short_token', $short)->exists());
                $guest->short_token = $short;
            }
        });
    }
}
