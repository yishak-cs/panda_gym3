<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'membership_plan_id',
        'startDate',
    ];

    protected $dates = ['startDate', 'endDate'];

    /**
     * Get the member associated with the Subscription
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Members::class, 'member_id');
    }

    /**
     * Get all of the checkins for the Subscription
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function checkins(): HasMany
    {
        return $this->hasMany(CheckIns::class, 'subscription_id');
    }

    /**
     * Get the membership_plan associated with the Subscription
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function membership_plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    /**
     * Get the end date attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function endDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value) : null,
            set: fn($value) => $value instanceof Carbon ? $value : $this->calculateEndDate(),
        );
    }

    protected static function booted()
    {
        static::creating(function ($subscription) {
            $subscription->endDate = $subscription->calculateEndDate();
        });
        static::updating(function ($subscription) {
            if ($subscription->isDirty('startDate') || $subscription->isDirty('membership_plan_id')) {
                $subscription->endDate = $subscription->calculateEndDate();
            }
        });
    }

    /**
     * Calculate the subscription end date.
     *
     * @return Carbon
     */
    protected function calculateEndDate(): Carbon
    {
        return $this->startDate->copy()->addDays($this->membership_plan->duration);
    }
    protected function calculateEndDateOnUpdate(): Carbon
    {
        return Carbon::parse($this->startDate)->copy()->addDays($this->membership_plan->duration);
    }

    /**
     * Update end dates for all affected subscriptions when plan duration changes
     * @param int $planId
     * @return void
     */
    public static function updateEndDatesForPlanChange(int $planId): void
    {
        // Only update pending and active subscriptions
        self::query()
            ->where('membership_plan_id', $planId)
            ->where(function ($query) {
                $query->pending()
                    ->orWhere(function ($q) {
                        $q->active();
                    });
            })
            ->chunk(100, function ($subscriptions) {
                foreach ($subscriptions as $subscription) {
                    $subscription->endDate = $subscription->calculateEndDateOnUpdate();
                    $subscription->save();
                }
            });
    }

    /**
     * Get the status attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->calculateStatus(),
        );
    }
    /**
     * Calculate the status of the subscription.
     *
     * @return string
     */
    protected function calculateStatus(): string
    {
        $now = now();

        if ($this->startDate->gt($now)) {
            return 'pending';
        } elseif ($this->startDate->lte($now) && $this->endDate->gt($now)) {
            return 'active';
        } else {
            return 'expired';
        }
    }
    /**
     * Scope a query to only include pending subscriptions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('startDate', '>', now());
    }

    public function scopeActive($query)
    {
        return $query->where('startDate', '<=', now())
            ->where('endDate', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('endDate', '<=', now());
    }
}
/**
 * $pendingSubscriptions = Subscription::pending()->get();
 * $activeSubscriptions = Subscription::active()->get();
 * $expiredSubscriptions = Subscription::expired()->get();
 */

/**
 * // Get active subscriptions for a specific member
 * $memberActiveSubscriptions = Subscription::active()->where('member_id', $memberId)->get();
 * Count expired subscriptions for a specific plan
 * $expiredCount = Subscription::expired()->where('membership_plan_id', $planId)->count();
 * Paginate pending subscriptions
 * $pendingSubscriptions = Subscription::pending()->paginate(15);
 */
