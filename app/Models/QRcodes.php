<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
