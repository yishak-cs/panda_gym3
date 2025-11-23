<?php

namespace App\Jobs;

use App\Mail\MailerService;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class NewJob implements ShouldQueue
{

    use Queueable;

    /**
     * The number of seconds to wait before retrying the queued listener.
     *
     * @var int
     */
    public $backoff = 30;
    protected $retryCount;
    /**
     * Create a new job instance.
     */
    public function __construct($retryCount = 0)
    {
        $this->retryCount = $retryCount;
    }

    /**
     * Determine the time at which the listener should timeout.
     */
    public function retryUntil(): DateTime
    {
        return now()->addMinutes(10);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $salesData = MembershipPlan::with('subscription')->get();
        $threeMonthsAgo = Carbon::now()->subMonths(1)->startOfMonth();
        $now = Carbon::now()->endOfMonth();
        $recentSubscriptions = Subscription::whereBetween('created_at', [$threeMonthsAgo, $now])
            ->with(['membership_plan'])
            ->get();

           Mail::to(config('variables.email'))->send(new MailerService($salesData, $recentSubscriptions));
    }

    public function failed()
    {
        if ($this->retryCount < 3) {
            self::dispatch($this->retryCount + 1)
                ->delay(today()->addDay());
        }
    }
}
