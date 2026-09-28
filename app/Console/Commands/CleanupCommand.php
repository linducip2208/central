<?php

namespace App\Console\Commands;

use App\Models\WebhookDelivery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanupCommand extends Command
{
    protected $signature = 'mbg:cleanup {--days=90 : Umur hari data yang dibersihkan}';

    protected $description = 'Bersihkan notifikasi terbaca lama, file import-tmp, delivery webhook DEAD lama.';

    public function handle(): int
    {
        $days = max(7, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $notif = DB::table('notifications')->whereNotNull('read_at')->where('read_at', '<', $cutoff)->delete();
        $this->info("Notifikasi terbaca dihapus: {$notif}");

        $tmp = 0;
        foreach (Storage::disk('local')->files('import-tmp') as $file) {
            if (Storage::disk('local')->lastModified($file) < $cutoff->timestamp) {
                Storage::disk('local')->delete($file);
                $tmp++;
            }
        }
        $this->info("File import-tmp dihapus: {$tmp}");

        $dead = WebhookDelivery::where('status', 'DEAD')->where('updated_at', '<', $cutoff)->delete();
        $this->info("Webhook DEAD dihapus: {$dead}");

        return self::SUCCESS;
    }
}
