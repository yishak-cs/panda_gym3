<?php

use App\Models\Members;
use App\Models\Subscription;
use App\Models\MembershipPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\UserAccessMiddleware;
use App\Jobs\NewJob;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
      'UserAccess' => UserAccessMiddleware::class
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })
  ->withSchedule(function (Schedule $schedule) {

    $memberCleanupTask = function ($runType = 'scheduled') {
      Log::channel('cleanup')->info("***** Starting member cleanup job ({$runType} run) *****");

      try {
        $membersQuery = Members::whereDoesntHave('active_subscription')
          ->whereDoesntHave('pending_subscription');

        $totalCount = $membersQuery->count();
        Log::channel('cleanup')->info("Found {$totalCount} members eligible for cleanup");

        if ($totalCount === 0) {
          Log::channel('cleanup')->info('No members found for cleanup');
          return;
        }

        $deletedCount = 0;

        $membersQuery->chunk(50, function ($members) use (&$deletedCount) {
          foreach ($members as $member) {
            try {
              Log::channel('cleanup')->info("Processing member [{$member->id}, {$member->firstname}]");

              $member->delete();
              $deletedCount++;

              Log::channel('cleanup')->info("Successfully deleted member {$member->id}");
            } catch (\Exception $e) {
              Log::channel('cleanup')->error("Failed to delete member {$member->id}: " . $e->getMessage());
            }
          }
        });

        Log::channel('cleanup')->info("Successfully deleted {$deletedCount} out of {$totalCount} members");
      } catch (\Exception $e) {
        Log::channel('cleanup')->error('Member cleanup job failed: ' . $e->getMessage());
      }

      Log::channel('cleanup')->info("##### Finished member cleanup job ({$runType} run) #####");
    };

    $schedule->call(function () use ($memberCleanupTask) {
      $memberCleanupTask('morning');
    })->weeklyOn(3, '07:30');

    $schedule->call(function () use ($memberCleanupTask) {
      $memberCleanupTask('evening');
    })->weeklyOn(3, '18:00');
  })
  ->withSchedule(function (Schedule $schedule) {

    $schedule->call(function () {
      $logger = Log::channel('cleanup');
      $logger->info('*****Starting Checkins cleanup job*****');

      $threeMonthsAgo = now()->startOfMonth()->subMonths(3);
      $deletedCount = DB::table('check_ins')
        ->join('subscriptions', 'check_ins.subscription_id', '=', 'subscriptions.id')
        ->where('subscriptions.endDate', '<=', $threeMonthsAgo)
        ->delete();

      $logger->info("Deleted {$deletedCount} old checkins");
      $logger->info('#####Finished Checkins cleanup job#####');
    })->cron('30 18 1 1,4,7,10 *');
  })
  ->withSchedule(function (Schedule $schedule) {
    // Yearly subscription cleanup - Runs on January 1st at 02:00
    $schedule->call(function () {
      $logger = Log::channel('cleanup');
      $logger->info('*****Starting Subscription cleanup job*****');

      $lastYear = now()->subYear()->endOfYear();
      Subscription::where('endDate', '<=', $lastYear)
        ->chunk(100, function ($subscriptions) {
          $subscriptions->each->delete();
        });

      $logger->info('#####Finished subscriptions cleanup job#####');
    })->yearlyOn(1, 1, '02:00');
  })->withSchedule(function (Schedule $schedule) {
    $schedule->call(function () {
      NewJob::dispatch();
    })->cron('30 7 * * 1,5');

    $schedule->call(function () {
      NewJob::dispatch();
    })->cron('0 18 * * 1,5');
  })->withSchedule(function (Schedule $schedule) {
    // Yearly membership plan cleanup - Runs on January 1st at 03:00
    $schedule->call(function () {
      $logger = Log::channel('cleanup');
      $logger->info('*****Starting Membership Plan cleanup job*****');

      $deletedCount = MembershipPlan::whereDoesntHave('subscription')->delete();
      $logger->info("Deleted {$deletedCount} orphaned membership plans");

      Log::channel('cleanup')->info('#####Finished Membership Plan cleanup job#####');
    })->yearlyOn(1, 7, '17:00');
  })
  ->create();
// pandafitness25@gmail.com
//yishak0907968056