<?php

namespace App\Console\Commands;

use App\Models\OtpCode;
use Illuminate\Console\Command;

class CleanupExpiredOtps extends Command
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:cleanup';

    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired OTP codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = OtpCode::where('expires_at', '<', now())->delete();

        $this->info("Deleted {$deleted} expired OTP codes.");

        return Command::SUCCESS;
    }
}
