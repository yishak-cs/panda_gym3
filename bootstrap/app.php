<?php

use App\Models\Members;
use App\Models\CheckIns;
use App\Mail\MailerService;
use App\Models\Subscription;
use App\Models\MembershipPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\UserAccessMiddleware;
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
    // Monthly member cleanup - Runs on the 1st of each month at 00:00
    $schedule->call(function () {
      Log::channel('cleanup')->info('*****Starting member cleanup job*****');

      $members = Members::get();
      Log::channel('cleanup')->info('Found ' . $members->count() . ' members');

      foreach ($members as $member) {
        Log::channel('cleanup')->info("Checking member {$member->id}");
        Log::channel('cleanup')->info("Active subscription: " . ($member->active_subscription ? 'yes' : 'no'));
        Log::channel('cleanup')->info("Pending subscription: " . ($member->pending_subscription ? 'yes' : 'no'));

        if ($member->active_subscription == null && $member->pending_subscription == null) {
          Log::channel('cleanup')->info("Deleting member {[$member->id, $member->firstname]}");
          DB::statement('SET FOREIGN_KEY_CHECKS=0;');
          $member->delete();
          DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
      }

      Log::channel('cleanup')->info('#####Finished member cleanup job#####');
    })->monthlyOn(1, '00:00');
  })
  ->withSchedule(function (Schedule $schedule) {
    // Quarterly checkins cleanup - Runs on the 1st day of January, April, July, and October at 01:00
    $schedule->call(function () {
      $logger = Log::channel('cleanup');
      $logger->info('*****Starting Checkins cleanup job*****');

      // Bulk delete for better performance
      $threeMonthsAgo = now()->startOfMonth()->subMonths(3);
      $deletedCount = DB::table('check_ins')
        ->join('subscriptions', 'check_ins.subscription_id', '=', 'subscriptions.id')
        ->where('subscriptions.endDate', '<=', $threeMonthsAgo)
        ->delete();

      $logger->info("Deleted {$deletedCount} old checkins");
      $logger->info('#####Finished Checkins cleanup job#####');
    })->cron('0 1 1 1,4,7,10 *');
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
      $salesData = MembershipPlan::with('subscription')->get();
      Mail::to(config('variables.email'))->send(new MailerService($salesData));
    })->everyMinute();
  })->withSchedule(function (Schedule $schedule) {
    // Yearly membership plan cleanup - Runs on January 1st at 03:00
    $schedule->call(function () {
      $logger = Log::channel('cleanup');
      $logger->info('*****Starting Membership Plan cleanup job*****');

      $deletedCount = MembershipPlan::whereDoesntHave('subscription')->delete();
      $logger->info("Deleted {$deletedCount} orphaned membership plans");

      Log::channel('cleanup')->info('#####Finished Membership Plan cleanup job#####');
    })->yearlyOn(1, 1, '03:00');
  })
  ->create();
