<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QRcodes extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'path',
    ];


    protected static function booted()
    {
        static::deleting(function ($qrCode) {
            if ($qrCode->path && Storage::disk('public')->exists($qrCode->path)) {
                Storage::disk('public')->delete($qrCode->path);
            }
        });
    }

    /**
     * Get the Member associated with this QRcode
     *
     * @return HasOne
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Members::class, 'member_id');
    }
}
