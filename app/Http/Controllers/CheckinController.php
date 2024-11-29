<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Members;
use App\Models\CheckIns;
use App\Models\CheckInTimes;
use Illuminate\Support\Facades\Crypt;

class CheckinController extends Controller
{
    public function processScan($encrypted_member_id)
    {
        try {
            $member_id = Crypt::decryptString($encrypted_member_id);
            $member_id = intval(substr($member_id, 2));
            $member = Members::find($member_id);
            // Rest of your checkin logic
            if (!$member->canCheckIn()) {
                return redirect()->route('members.show', ['id' => $member_id])->with('error', 'Either You have reached the maximum number of entries for this membership or the Start Date is not due');
            }

            $todayCheckin = $member->checkins()->whereDate('date', Carbon::today())->first();

            if ($todayCheckin) {
                $this->updateCheckin($todayCheckin->id);
                return redirect()->route('members.show', ['id' => $member_id])->with('success', 'Check-in successful.');
            } else {
                $checkin = new CheckIns([
                    'member_id' => $member_id,
                    'subscription_id' => $member->activeSubscription()->id,
                    'in_times' => 1,
                    'date' => Carbon::today(),
                    'status' => 'success',
                ]);

                if ($checkin->save()) {
                    $checkInTime = new CheckInTimes([
                        'checkin_id' => $checkin->id,
                    ]);
                    $checkInTime->save();
                    return redirect()->route('members.show', ['id' => $member_id])->with('success', 'Check-in successful.');
                }
            }
        } catch (\Exception $e) {
            // Handle decryption errors

        }
    }

    public function updateCheckin(int $checkin_id)
    {
        $checkin = CheckIns::find($checkin_id);
        $checkin->in_times++;
        $checkin->save();

        $checkInTime = new CheckInTimes([
            'checkin_id' => $checkin_id,
        ]);
        $checkInTime->save();
    }
}
