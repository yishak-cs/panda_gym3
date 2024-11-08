<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CheckInTimes extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkin_id',
    ];

    /**
     * Get the CheckIn that owns the CheckInTimes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function CheckIns(): BelongsTo
    {
        return $this->belongsTo(CheckIns::class, 'checkin_id');
    }
}
