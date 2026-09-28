<?php

namespace App\Console\Commands;

use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupCommand extends Command
{
    protected $signature = 'mbg:backup {--keep=7 : Jumlah backup harian yang dipertahankan}';

    protected $description = 'Backup database (mysqldump bila tersedia, fallback tenant JSON) + pruning retensi.';

    public function handle(): int
    {
        $date = now()->format('Ymd-His');
        $dir = 'backups';
        Storage::disk('local')->makeDirectory($dir);

        $connection = config('database.default');
        $done = false;
        if ($connection === 'mysql') {
            $done = $this->dumpMysql("{$dir}/db-{$date}.sql");
        }
        if (! $done) {
            // Fallback aman: export JSON per organisasi.
            foreach (Organization::pluck('id') as $orgId) {
                $this->call('mbg:tenant-export', ['organization' => $orgId]);
            }
            $this->info('mysqldump tidak tersedia — fallback tenant JSON selesai.');
        }

        // Pruning retensi.
        $files = collect(Storage::disk('local')->files($dir))->sort()->values();
        $keep = max(1, (int) $this->option('keep'));
        foreach ($files->slice(0, max(0, $files->count() - $keep)) as $old) {
            Storage::disk('local')->delete($old);
            $this->info('Hapus backup lama: '.$old);
        }

        return self::SUCCESS;
    }

    protected function dumpMysql(string $path): bool
    {
        $which = trim((string) shell_exec('where mysqldump 2>NUL || which mysqldump 2>/dev/null'));
        if ($which === '') {
            return false;
        }
        $full = Storage::disk('local')->path($path);
        $cmd = sprintf(
            '"%s" --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            explode("\n", $which)[0],
            escapeshellarg((string) config('database.connections.mysql.host')),
            escapeshellarg((string) config('database.connections.mysql.port')),
            escapeshellarg((string) config('database.connections.mysql.username')),
            escapeshellarg((string) config('database.connections.mysql.password')),
            escapeshellarg((string) config('database.connections.mysql.database')),
            escapeshellarg($full)
        );
        exec($cmd, $out, $code);
        if ($code !== 0 || ! is_file($full) || filesize($full) < 100) {
            @unlink($full);

            return false;
        }
        $this->info('Backup database: '.$path.' ('.number_format(filesize($full)).' bytes)');

        return true;
    }
}
