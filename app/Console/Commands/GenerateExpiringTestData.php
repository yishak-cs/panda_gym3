<?php

namespace App\Console\Commands;

use App\Models\Members;
use App\Models\CheckIns;
use App\Models\CheckInTimes;
use App\Models\Subscription;
use App\Models\MembershipPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateExpiringTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:expiring-test-data {--count=20 : Number of test members to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate dummy data for testing expiring subscriptions scenarios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');

        $this->info("Generating {$count} test members with various expiration scenarios...");

        // Get or create test membership plans
        $plans = $this->getOrCreatePlans();

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        // Track counts per scenario
        $scenarioCounts = array_fill(0, 8, 0);

        DB::transaction(function () use ($count, $plans, $bar, &$scenarioCounts) {
            for ($i = 0; $i < $count; $i++) {
                $scenario = $i % 8; // Cycle through 8 different scenarios

                $member = $this->createMember();
                $subscription = $this->createSubscription($member, $plans, $scenario);
                $this->createCheckIns($member, $subscription, $scenario);

                $scenarioCounts[$scenario]++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ Successfully generated {$count} test members!");
        $this->newLine();
        $this->info("Scenarios created:");
        $this->line("  1. Expiring today by date: {$scenarioCounts[0]} members");
        $this->line("  2. Expiring in 5 days by date: {$scenarioCounts[1]} members");
        $this->line("  3. Expired 3 days ago by date: {$scenarioCounts[2]} members");
        $this->line("  4. Expired 10 days ago by date: {$scenarioCounts[3]} members");
        $this->line("  5. Expired by entries (exhausted yesterday, end_date in future): {$scenarioCounts[4]} members");
        $this->line("  6. Expired by entries (exhausted 5 days ago, end_date in future): {$scenarioCounts[5]} members");
        $this->line("  7. Expiring in 7 days by date, but entries almost exhausted: {$scenarioCounts[6]} members");
        $this->line("  8. Normal active subscription (expires in 15 days): {$scenarioCounts[7]} members");
    }

    /**
     * Get or create test membership plans
     */
    private function getOrCreatePlans()
    {
        $plans = [];

        // Plan with entry limit
        $limitedPlan = MembershipPlan::firstOrCreate(
            ['name' => 'Test Limited Plan'],
            [
                'duration' => 30,
                'price' => 500,
                'allowed_entries' => 10,
                'description' => 'Test plan with 10 entry limit'
            ]
        );
        $plans['limited'] = $limitedPlan;

        // Plan without entry limit
        $unlimitedPlan = MembershipPlan::firstOrCreate(
            ['name' => 'Test Unlimited Plan'],
            [
                'duration' => 30,
                'price' => 800,
                'allowed_entries' => null,
                'description' => 'Test plan without entry limit'
            ]
        );
        $plans['unlimited'] = $unlimitedPlan;

        return $plans;
    }

    /**
     * Create a test member
     */
    private function createMember()
    {
        $faker = \Faker\Factory::create();

        return Members::create([
            'firstname' => $faker->firstName,
            'lastname' => $faker->lastName,
            'email' => $faker->unique()->email,
            'phone_number' => $faker->phoneNumber,
            'sex' => $faker->randomElement(['male', 'female']),
            'goal' => $faker->randomElement(['Lose Weight', 'Gain Weight', 'Maintain Weight']),
            'current_weight' => $faker->numberBetween(50, 100),
            'target_weight' => $faker->numberBetween(50, 100),
            'length' => $faker->numberBetween(150, 200),
        ]);
    }

    /**
     * Create subscription based on scenario
     */
    private function createSubscription($member, $plans, $scenario)
    {
        $today = Carbon::today();

        switch ($scenario) {
            case 0: // Expiring today by date
                $startDate = $today->copy()->subDays(30);
                $plan = $plans['unlimited'];
                break;

            case 1: // Expiring in 5 days by date
                $startDate = $today->copy()->subDays(25);
                $plan = $plans['unlimited'];
                break;

            case 2: // Expired 3 days ago by date
                $startDate = $today->copy()->subDays(33);
                $plan = $plans['unlimited'];
                break;

            case 3: // Expired 10 days ago by date
                $startDate = $today->copy()->subDays(40);
                $plan = $plans['unlimited'];
                break;

            case 4: // Expired by entries (exhausted yesterday, end_date in future)
                $startDate = $today->copy()->subDays(10);
                $plan = $plans['limited'];
                break;

            case 5: // Expired by entries (exhausted 5 days ago, end_date in future)
                $startDate = $today->copy()->subDays(10);
                $plan = $plans['limited'];
                break;

            case 6: // Expiring in 7 days by date, but entries almost exhausted
                $startDate = $today->copy()->subDays(23);
                $plan = $plans['limited'];
                break;

            case 7: // Normal active subscription
                $startDate = $today->copy()->subDays(15);
                $plan = $plans['unlimited'];
                break;

            default:
                $startDate = $today->copy()->subDays(15);
                $plan = $plans['unlimited'];
        }

        return Subscription::create([
            'member_id' => $member->id,
            'membership_plan_id' => $plan->id,
            'startDate' => $startDate,
        ]);
    }

    /**
     * Create check-ins based on scenario
     */
    private function createCheckIns($member, $subscription, $scenario)
    {
        $today = Carbon::today();
        $plan = $subscription->membership_plan;

        switch ($scenario) {
            case 4: // Expired by entries (exhausted yesterday)
                // Create 10 check-ins, last one yesterday (exhausts on yesterday)
                // Start from 9 days ago, end yesterday
                for ($i = 0; $i < 10; $i++) {
                    $checkinDate = $today->copy()->subDays(1)->subDays(9 - $i);
                    $checkin = CheckIns::create([
                        'member_id' => $member->id,
                        'subscription_id' => $subscription->id,
                        'in_times' => 1,
                        'date' => $checkinDate,
                        'status' => 'success',
                    ]);

                    CheckInTimes::create([
                        'checkin_id' => $checkin->id,
                    ]);
                }
                break;

            case 5: // Expired by entries (exhausted 5 days ago)
                // Create 10 check-ins, last one 5 days ago (exhausts on 5 days ago)
                // Start from 14 days ago, end 5 days ago
                for ($i = 0; $i < 10; $i++) {
                    $checkinDate = $today->copy()->subDays(5)->subDays(9 - $i);
                    $checkin = CheckIns::create([
                        'member_id' => $member->id,
                        'subscription_id' => $subscription->id,
                        'in_times' => 1,
                        'date' => $checkinDate,
                        'status' => 'success',
                    ]);

                    CheckInTimes::create([
                        'checkin_id' => $checkin->id,
                    ]);
                }
                break;

            case 6: // Expiring in 7 days, but entries almost exhausted (9/10)
                // Create 9 check-ins
                for ($i = 0; $i < 9; $i++) {
                    $checkinDate = $today->copy()->subDays(20 - $i);
                    $checkin = CheckIns::create([
                        'member_id' => $member->id,
                        'subscription_id' => $subscription->id,
                        'in_times' => 1,
                        'date' => $checkinDate,
                        'status' => 'success',
                    ]);

                    CheckInTimes::create([
                        'checkin_id' => $checkin->id,
                    ]);
                }
                break;

            default:
                // Create some random check-ins for other scenarios
                $checkinCount = rand(0, 5);
                for ($i = 0; $i < $checkinCount; $i++) {
                    $daysAgo = rand(1, 20);
                    $checkinDate = $today->copy()->subDays($daysAgo);
                    $checkin = CheckIns::create([
                        'member_id' => $member->id,
                        'subscription_id' => $subscription->id,
                        'in_times' => 1,
                        'date' => $checkinDate,
                        'status' => 'success',
                    ]);

                    CheckInTimes::create([
                        'checkin_id' => $checkin->id,
                    ]);
                }
        }
    }
}
