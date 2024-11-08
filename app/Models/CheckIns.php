<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CheckIns extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'in_times',
        'date',
        'subscription_id',
        'status',
    ];

    /**
     * Get the member that owns the CheckIns
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Members::class, 'member_id');
    }

    /**
     * Get the subscription the member used to checkin
     * 
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function susbscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    /**
     * Get the checkin times for the checkin
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function checkInTimes(): HasMany
    {
        return $this->hasMany(CheckInTimes::class, 'checkin_id');
    }
}
