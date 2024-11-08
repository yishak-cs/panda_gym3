<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Members;
use App\Models\CheckIns;
use App\Models\CheckInTimes;

class CheckinController extends Controller
{
    public function processScan(int $member_id)
    {
        $member = Members::find($member_id);
        if (!$member->canCheckIn()) {
            return redirect()->back()->with('error', 'You have reached the maximum number of entries for this membership.');
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
                return redirect()->route('members.show', ['id' => $member_id])->with('success', 'Check-in successful.');
            }
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
