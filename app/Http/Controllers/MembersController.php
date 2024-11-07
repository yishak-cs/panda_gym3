<?php

namespace App\Http\Controllers;

use App\Models\Members;
use App\Models\QRcodes;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\DTO\GymAccessPayload;
use App\Jobs\SendQRCodeEmail;
use App\Models\MembershipPlan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

                $payload = new GymAccessPayload(
                    memberId: $newMember->id,
                    subscriptionId: $subscription->id,
                    membershipName: $subscription->membership_plan->name,
                    endDate: $subscription->endDate,
                    timestamp: now()
                );

                $encryptedPayload = Crypt::encrypt($payload);

                $url = route('Checkin', ['payload' => $encryptedPayload]);

                // Generate QR code
                $qrCode = QrCode::size(300)
                    ->errorCorrection('H')
                    ->generate($url);
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
