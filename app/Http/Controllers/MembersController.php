<?php

namespace App\Http\Controllers;

use App\Models\Members;
use App\Models\QRcodes;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\MembershipPlan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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

                $url = route('Checkin', ['member_id' => $newMember->id]);

                // Generate QR code
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
                ->with('error', 'Failed to add member: ' . $e->getMessage())
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

        // Get subscription (active, pending, or expired)
        $subscription = $member->active_subscription
            ?? $member->pending_subscription
            ?? $member->subscriptions()->expired()->latest()->first();

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
     * Display the form for editing a member.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function apiShow($id)
    {
        $member = Members::findOrFail($id);
        return response()->json($member);
    }

    /**
     * Update a member.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function apiUpdate(Request $request, $id)
    {
        $member = Members::findOrFail($id);

        $validatedData = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:members,email,' . $member->id,
            'phone_number' => 'required|string|max:255',
            'sex' => 'required|string|in:male,female,other',
            'current_weight' => 'nullable|numeric',
            'target_weight' => 'nullable|numeric',
            'goal' => 'nullable|string|max:255',
        ]);

        $member->update($validatedData);

        return response()->json(['message' => 'Member updated successfully', 'member' => $member]);
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
