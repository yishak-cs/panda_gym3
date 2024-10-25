<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MembershipRenewal extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'previous_plan',
        'new_plan',
        'price'
    ];

    /**
     * Get the member that owns the MembershipRenewal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Members::class, 'member_id');
    }
    /**
     * Get the previous plan of the member who renewed 
     *
     * @return BelongsTo
     */
    public function previousPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'previous_plan');
    }

    /**
     * Get the new plan of the member who renewed 
     *
     * @return BelongsTo
     */
    public function newPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'new_plan');
    }
}
