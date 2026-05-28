<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Candidate;
use Illuminate\Support\Facades\Log;

class ExpireOfferings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offerings:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire offering tokens that have passed their expiration date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        // Find candidates where status is 'Applied', 'In Progress', or 'Offered', token is not null, and token is expired
        $expiredCandidates = Candidate::whereIn('status', ['Applied', 'In Progress', 'Offered'])
            ->whereNotNull('offering_token')
            ->where('offering_token_expires_at', '<', $now)
            ->get();

        $count = $expiredCandidates->count();

        if ($count > 0) {
            foreach ($expiredCandidates as $candidate) {
                $candidate->update([
                    'status' => 'Expired',
                    'offering_token' => null,
                    'offering_token_expires_at' => null,
                ]);
            }

            $message = "Expired {$count} offering letters successfully.";
            $this->info($message);
            Log::info($message);
        } else {
            $this->info('No expired offerings found.');
        }

        return Command::SUCCESS;
    }
}
