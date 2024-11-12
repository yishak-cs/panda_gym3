<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;


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
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'member_id');
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
     * Get all of the checkins for the Members
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function checkins(): HasMany
    {
        return $this->hasMany(CheckIns::class, 'member_id');
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
    /**
     * get the user's active subscription
     *
     * @return Subscription|null
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->active()
            ->latest()
            ->first();
    }

    /**
     * get the user's active subscription as an attribute 
     * this is needed for members/list view
     *
     * @return Subscription|null
     */
    public function getActiveSubscriptionAttribute()
    {
        return $this->subscriptions()
            ->active()
            ->latest()
            ->first();
    }

    /**
     * get the user's pending subscription
     * this is needed for members/list view
     * @return Subscription|null
     */
    public function getPendingSubscriptionAttribute()
    {
        return $this->subscriptions()
            ->pending()
            ->latest()
            ->first();
    }
    /**
     * Checks if the member can check in based on their active subscription.
     * 
     * This method first checks if the member has an active subscription. If not, it returns false.
     * If the subscription has a limited number of allowed entries, it counts the number of check-ins
     * for the current subscription and compares it to the allowed entries. If the count exceeds
     * the allowed entries, it returns false. Otherwise, it returns true, indicating the member can check in.
     * 
     * @return boolean True if the member can check in, false otherwise.
     */
    public function canCheckIn()
    {
        $subscription = $this->activeSubscription();

        if (!$subscription) {
            return false;
        }

        // If allowed_entries is not null, check the count
        if (!is_null($subscription->membership_plan->allowed_entries)) {
            $checkInsCount = count($this->checkins()
                ->where('subscription_id', $subscription->id));

            if ($checkInsCount >= $subscription->membershipPlan->allowed_entries) {
                return false;
            }
        }

        return true;
    }
}
