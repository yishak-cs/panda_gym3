<?php

namespace App\Http\Controllers;

use App\Models\Members;
use App\Models\QRcodes;
use Illuminate\Support\Arr;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\MembershipPlan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\MailController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Controller for managing gym members.
 */
class MembersController extends Controller
{
    /**
     * Show the form for adding a new member.
     *
     * @return \Illuminate\View\View
     */
    public function add()
    {
        $memberships = MembershipPlan::all();
        if (Auth::user()->role === 'receptionist') {
            return view('content.Receptionist.members.add-member', compact('memberships'));
        }
        return view('content.Admin.members.add-member', compact('memberships'));
    }

    /**
     * Store a newly created member in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required',
            'sex' => 'required',
            'length' => 'numeric|nullable',
            'membership_plan' => 'required',
            'startDate' => 'required|date',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Create new member
                $memberInfo = $request->only([
                    'firstname',
                    'lastname',
                    'email',
                    'phone_number',
                    'sex',
                    'length',
                    'current_weight',
                    'target_weight',
                    'goal',
                ]);

                $newMember = Members::create($memberInfo);

                // Create subscription
                $subscription = new Subscription([
                    'member_id' => $newMember->id,
                    'membership_plan_id' => $request->membership_plan,
                    'startDate' => Carbon::parse($request->startDate),
                ]);
                $subscription->save();

                // Encrypt just the member ID
                $encrypted = Crypt::encrypt($newMember->id);

                // Generate QR code with encrypted member ID
                $url = route('Checkin', ['member_id' => $encrypted]);
                $qrCode = QrCode::format('png')->size(300)->generate($url);

                $qrCodePath = 'qrcodes/' . $newMember->id . '.png';

                // Store QR code file
                Storage::disk('public')->put($qrCodePath, $qrCode);

                // Create QR code record
                QRcodes::create([
                    'member_id' => $newMember->id,
                    'path' => $qrCodePath,
                ]);
                // Dispatch email job with fresh subscription data
                MailController::sendQRcode($subscription);
            });

            return redirect()->back()->with('success', 'Member added successfully');
        } catch (\Exception $e) {
            Log::error('Member registration failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to add member')
                ->withInput();
        }
    }

    /**
     * Display a list of members.
     *
     * @return \Illuminate\View\View
     */
    public function list()
    {
        $members = Members::get();
        if (Auth::user()->role === 'receptionist') {
            return view('content.Receptionist.members.list-member', compact('members'));
        }
        return view('content.Admin.members.list-member', compact('members'));
    }

    /**
     * Display the details of a member.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Eager load relationships to avoid N+1 queries
        $member = Members::findOrFail($id);

        // Get subscription - try active first, then pending, then most recent subscription
        $subscription = $member->active_subscription
            ?? $member->pending_subscription
            ?? $member->subscriptions()
            ->with('membership_plan')
            ->where('startDate', '<=', now())
            ->latest()
            ->first();

        // If still no subscription, try to get any subscription (including future ones)
        if (!$subscription) {
            $subscription = $member->subscriptions()
                ->with('membership_plan')
                ->latest()
                ->first();
        }

        if (!$subscription) {
            return redirect()->back()->with('error', 'No subscription found for this member');
        }

        // Prepare check-in data for the contribution graph
        $checkinData = $member->checkins()
            ->where('subscription_id', $subscription->id)
            ->get()
            ->groupBy(function ($checkin) {
                return Carbon::parse($checkin->date)->format('Y-m-d');
            })
            ->map(function ($dayCheckins) {
                return [
                    'count' => $dayCheckins->sum('in_times'),
                    'times' => $dayCheckins->flatMap(function ($checkin) {
                        return $checkin->checkInTimes->pluck('created_at')
                            ->map(fn($time) => $time->format('H:i'));
                    })->toArray()
                ];
            });

        $count_check = $member->checkins()
            ->where('subscription_id', $subscription->id)
            ->sum('in_times');


        if (Auth::user()->role === 'receptionist') {
            return view('content.Receptionist.members.show-member', compact(
                'member',
                'subscription',
                'checkinData',
                'count_check'
            ));
        }
        return view('content.Admin.members.show-member', compact(
            'member',
            'subscription',
            'checkinData',
            'count_check'
        ));
    }

    /**
     * Display the details of a member.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function destroy($id)
    {
        $member = Members::find($id);
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $member->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return redirect()->back()->with('success', 'Member deleted successfully');
    }
    /**
     * Display member data and available membership plans
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function apiShow($id)
    {
        $member = Members::with(['subscriptions' => function ($query) {
            $query->with('membership_plan')
                ->orderBy('startDate', 'desc');
        }])->findOrFail($id);

        // Get current active subscription if exists
        $activeSubscription = $member->activeSubscription();

        // Get all membership plans
        $availablePlans = MembershipPlan::all();

        // Calculate earliest possible start date for new subscription
        $earliestStartDate = null;

        // Prepare subscription info for response
        $subscriptionInfo = null;
        if ($activeSubscription) {
            // Check if subscription has exceeded allowed entries
            $checkinCount = 0;
            $check_ins = $member->checkins()
                ->where('subscription_id', $activeSubscription->id)
                ->get();
            foreach ($check_ins as $check_in) {
                $checkinCount += $check_in->in_times;
            }

            $isExpiredByEntries = !is_null($activeSubscription->membership_plan->allowed_entries) &&
                $checkinCount >= $activeSubscription->membership_plan->allowed_entries;

            if (!$isExpiredByEntries) {
                $subscriptionInfo = [
                    'plan_name' => $activeSubscription->membership_plan->name,
                    'end_date' => $activeSubscription->endDate->format('Y-m-d'),
                    'entries_used' => $checkinCount,
                    'entries_allowed' => $activeSubscription->membership_plan->allowed_entries,
                    'is_limited' => !is_null($activeSubscription->membership_plan->allowed_entries)
                ];
                $earliestStartDate = $activeSubscription->endDate->addDay()->format('Y-m-d');
            }
        }

        return response()->json([
            'member' => $member,
            'subscription_info' => $subscriptionInfo,
            'available_plans' => $availablePlans,
            'earliest_start_date' => $earliestStartDate ?? now()->format('Y-m-d')
        ]);
    }

    /**
     * Update member information
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function apiUpdate(Request $request, $id)
    {
        $member = Members::findOrFail($id);

        // Validate member data
        $validatedData = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:members,email,' . $member->id,
            'phone_number' => 'required|string|max:255',
            'sex' => 'required|string|in:male,female,other',
            'length' => 'nullable|numeric',
            'current_weight' => 'nullable|numeric',
            'target_weight' => 'nullable|numeric',
            'goal' => 'nullable|string|max:255',
        ]);

        try {
            $member->update($validatedData);

            return response()->json([
                'message' => 'Member updated successfully',
                'member' => $member->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update member information'
            ], 422);
        }
    }

    /**
     * Handle subscription renewal
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function apiRenewSubscription(Request $request, $id)
    {
        $member = Members::findOrFail($id);

        $validatedData = $request->validate([
            'membership_plan' => 'required|exists:membership_plans,id',
            'startDate' => 'required|date'
        ]);

        try {
            $startDate = Carbon::parse($validatedData['startDate']);

            // Check for subscription overlap
            $activeSubscription = $member->activeSubscription();
            if ($activeSubscription && $startDate->lte($activeSubscription->endDate)) {
                throw new \Exception('New subscription cannot overlap with active subscription.');
            }

            // Check for any future subscriptions that might overlap
            $futureSubscription = $member->subscriptions()
                ->where('startDate', '>', now())
                ->where('startDate', '<=', $startDate)
                ->first();

            if ($futureSubscription) {
                throw new \Exception('New subscription conflicts with a pending subscription.');
            }

            // Create new subscription
            $subscription = $member->renewSubscription(
                $validatedData['membership_plan'],
                $startDate
            );

            // Dispatch email job with fresh subscription data
            MailController::sendQRcode($subscription);

            return response()->json([
                'message' => 'Subscription renewed successfully',
                'subscription' => $subscription->load('membership_plan')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display members with subscriptions expiring or expired within specified days.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function expiringSoon(Request $request)
    {
        // Validate date parameter
        $validated = $request->validate([
            'date' => 'nullable|date'
        ]);
        
        // Get date parameter, default to today
        $targetDate = isset($validated['date']) 
            ? Carbon::parse($validated['date'])->startOfDay()
            : Carbon::today()->startOfDay();
        
        // Calculate days difference for display purposes
        $days = $targetDate->diffInDays(Carbon::today()->startOfDay(), false);

        // Get all members with their subscriptions
        $allMembers = Members::with(['subscriptions.membership_plan', 'checkins'])->get();

        $expiringMembers = collect();

        foreach ($allMembers as $member) {
            // Get the most recent subscription that was active or is currently active
            $subscription = $member->subscriptions()
                ->with('membership_plan')
                ->where('startDate', '<=', now())
                ->latest()
                ->first();

            if (!$subscription) {
                continue;
            }

            // Check if expired by entries
            $checkinCount = 0;
            $isExpiredByEntries = false;
            $entriesExhaustionDate = null;

            if (!is_null($subscription->membership_plan->allowed_entries)) {
                $checkins = $member->checkins()
                    ->where('subscription_id', $subscription->id)
                    ->orderBy('date', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                $checkinCount = $checkins->sum('in_times');
                $isExpiredByEntries = $checkinCount >= $subscription->membership_plan->allowed_entries;

                // If expired by entries, find the date when entries were exhausted
                if ($isExpiredByEntries) {
                    $runningTotal = 0;
                    foreach ($checkins as $checkin) {
                        $runningTotal += $checkin->in_times;
                        if ($runningTotal >= $subscription->membership_plan->allowed_entries) {
                            $entriesExhaustionDate = Carbon::parse($checkin->date);
                            break;
                        }
                    }
                }
            }

            // Check if expired by date
            $isExpiredByDate = $subscription->endDate->lte(now());
            $endDate = $subscription->endDate;

            // Determine actual expiration date
            // If expired by entries, use the date entries were exhausted
            // Otherwise, use the end_date
            if ($isExpiredByEntries && $entriesExhaustionDate) {
                // If expired by entries, use the exhaustion date as the actual expiration date
                $actualExpirationDate = $entriesExhaustionDate->copy()->startOfDay();
            } else {
                // Use end_date for date-based expiration
                $actualExpirationDate = $endDate->copy()->startOfDay();
            }

            // Check if expiration date matches target date (exact day match)
            if ($actualExpirationDate->isSameDay($targetDate)) {
                // Calculate days difference for display purposes
                $daysDifference = $actualExpirationDate->diffInDays(now()->startOfDay(), false);
                $expiringMembers->push([
                    'member' => $member,
                    'subscription' => $subscription,
                    'expiration_date' => $actualExpirationDate,
                    'expired_by_entries' => $isExpiredByEntries,
                    'expired_by_date' => $isExpiredByDate,
                    'checkin_count' => $checkinCount,
                    'allowed_entries' => $subscription->membership_plan->allowed_entries,
                    'days_until_expiration' => $daysDifference,
                    'entries_exhaustion_date' => $entriesExhaustionDate,
                ]);
            }
        }

        // Sort by expiration date
        $expiringMembers = $expiringMembers->sortBy(function ($item) {
            return $item['expiration_date']->timestamp;
        });

        if (Auth::user()->role === 'receptionist') {
            return view('content.Receptionist.members.expiring-soon', compact('expiringMembers', 'days', 'targetDate'));
        }
        return view('content.Admin.members.expiring-soon', compact('expiringMembers', 'days', 'targetDate'));
    }

    /**
     * Constructor for MembersController.
     * Applies the 'auth' middleware to all methods in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
}
