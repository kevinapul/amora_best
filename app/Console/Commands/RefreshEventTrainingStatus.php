<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EventTraining;

class RefreshEventTrainingStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'event:refresh-status';

    /**
     * The console command description.
     */
    protected $description = 'Refresh status event training berdasarkan tanggal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('⏳ Refresh status event training...');

        $events = EventTraining::whereNotIn('status', ['done'])
            ->whereNotNull('tanggal_start')
            ->whereNotNull('tanggal_end')
            ->get();

        $count = 0;

        foreach ($events as $event) {
            $old = $event->status;
            $event->refreshStatus();

            if ($old !== $event->fresh()->status) {
                $count++;
                $this->line(
                    "✔ Event #{$event->id} : {$old} → {$event->status}"
                );
            }
        }

        $this->info("✅ Selesai. {$count} event berubah status.");
        return Command::SUCCESS;
    }
}
