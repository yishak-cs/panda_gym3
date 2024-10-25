<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Members extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone_number',
        'sex',
        'goal',
        'current_weight',
        'target_weight',
    ];

    /**
     * get subscription associated with the member
     *
     * @return HasOne
     */
    public function subscriptions(): HasOne
    {
        return $this->hasOne(Subscription::class, 'member_id');
    }

    /**
     * Get the member's QRcode
     *
     * @return HasOne
     */
    public function qrCode(): HasOne
    {
        return $this->hasOne(QRcodes::class, 'member_id');
    }

    /**
     * The notifications sent to the Member
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, 'member_notification', 'member_id', 'notification_id');
    }

    /**
     * Get all of the checkins for the Members
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function checkins(): HasMany
    {
        return $this->hasMany(CheckIns::class, 'member_id');
    }

    /**
     * Get all of the renewals for the Members
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function renewals(): HasMany
    {
        return $this->hasMany(MembershipRenewal::class, 'member_id');
    }
    /**
     * return the members full name
     *
     * @return string
     */
    public function getName(): string
    {
        $name = "";
        if (!empty($this->firstname)) {
            $name .= $this->firstname;
        }
        if (!empty($this->lastname)) {
            $name .= " " . $this->lastname;
        }
        return $name;
    }
}
