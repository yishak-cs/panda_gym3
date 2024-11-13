<?php

use App\Models\CheckIns;
use App\Models\Members;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    //
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })
  ->withSchedule(function (Schedule $schedule) {
    // Monthly member cleanup
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
    })->monthly();
  })
  ->withSchedule(function (Schedule $schedule) {
    // Quarterly checkins cleanup
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
    })->quarterly();
  })
  ->withSchedule(function (Schedule $schedule) {
    $schedule->call(function () {
      $logger = Log::channel('cleanup');
      $logger->info('*****Starting Subscription cleanup job*****');

      $lastYear = now()->subYear()->endOfYear();
      Subscription::where('endDate', '<=', $lastYear)
        ->chunk(100, function ($subscriptions) {
          $subscriptions->each->delete();
        });

      $logger->info('#####Finished subscriptions cleanup job#####');
    })->yearly();
  })
  ->withSchedule(function (Schedule $schedule) {
    $schedule->call(function () {
      $logger = Log::channel('cleanup');
      $logger->info('*****Starting Membership Plan cleanup job*****');

      $deletedCount = MembershipPlan::whereDoesntHave('subscription')->delete();
      $logger->info("Deleted {$deletedCount} orphaned membership plans");

      Log::channel('cleanup')->info('#####Finished Membership Plan cleanup job#####');
    })->yearly();
  })
  ->create();
