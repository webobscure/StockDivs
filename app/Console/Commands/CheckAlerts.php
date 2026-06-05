<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class CheckAlerts extends Command
{
    protected $signature = 'alerts:check';

    protected $description = 'Check active price and percent-change alerts.';

    public function handle(AlertService $alerts): int
    {
        $triggered = $alerts->checkActiveAlerts();
        $this->info("Triggered {$triggered} alert(s).");

        return self::SUCCESS;
    }
}
