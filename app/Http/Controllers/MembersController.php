<?php

namespace App\Http\Controllers;

use App\Models\Members;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\MembershipPlan;
use App\Models\QRcodes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
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
        return view('content.members.add-member', compact('memberships'));
    }

    /**
     * Store a newly created member in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required',
            'sex' => 'required',
            'membership_plan' => 'required',
        ]);

        // Extract member information from the request
        $memberinfo = $request->only([
            'firstname',
            'lastname',
            'email',
            'phone_number',
            'sex',
            'current_weight',
            'target_weight',
            'goal',
        ]);

        // Check if the member already exists and has a subscription
        $member = Members::where('email', $memberinfo['email'])->first();
        if ($member && Subscription::where('member_id', $member->id)->exists()) {
            return redirect()->back()->with('error', 'Member already exists');
        }

        // Create new member and subscription
        $newMember = Members::create($memberinfo);
        if ($newMember) {
            // Create new subscription
            $subscription = new Subscription([
                'member_id' => $newMember->id,
                'membership_plan_id' => $request->membership_plan,
                'startDate' =>  Carbon::parse($request->startDate),
            ]);

            /**
             * Now you can do date operations easily
             *   $daysLeft = $subscription->endDate->diffInDays(now());
             */

            if ($subscription->save()) {
                // Generate QR code data
                $qrCodeData = json_encode([
                    'id' => $newMember->id,
                    'name' => $newMember->getName(),
                    'membership_plan' => $newMember->subscriptions->membership_plan_id,
                ]);

                $qrCode = QrCode::format('svg')->generate($qrCodeData);
                $qrCodePath = 'qrcodes/' . $newMember->id . '.svg';

                Storage::disk('public')->put($qrCodePath, $qrCode);

                // Store QR code information in database
                QRcodes::create([
                    'member_id' => $newMember->id,
                    'path' => $qrCodePath,
                ]);

                return redirect()->route('Members-list')->with('success', 'Member added successfully');
            }
        }
        return redirect()->back()->with('error', 'Failed to add member');
    }

    /**
     * Display a list of members.
     *
     * @return \Illuminate\View\View
     */
    public function list()
    {
        $members = Members::all();
        return view('content.members.list-member', compact('members'));
    }

    /**
     * Display the details of a member.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $member = Members::find($id);
        return view('content.members.show-member', compact('member'));
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
        $member->delete();
        return redirect()->route('Members-list')->with('success', 'Member deleted successfully');
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
