<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MembershipPlan extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'duration',
        'price',
        'allowed_entries',
        'description',
    ];

    // update the endDate of all the pending and active subscriptions of subscribers of this plan
    protected static function booted()
    {
        static::updated(function ($plan) {
            if ($plan->isDirty('duration')) {
                Subscription::updateEndDatesForPlanChange($plan->id);
            }
        });
    }

    /**
     * Get all of the subscription for the MembershipPlan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscription(): HasMany
    {
        return $this->hasMany(Subscription::class, 'membership_plan_id');
    }
}
