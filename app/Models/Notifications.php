<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notifications extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'message',
        'sent_at',
        'status',
    ];

    /**
     * Get the recipiants ot this notification
     *
     * @return BelongsToMany
     */
    public function member(): BelongsToMany
    {
        return $this->belongsToMany(Members::class, 'member_notification', 'notification_id', 'member_id')->withTimestamps();
    }
}
