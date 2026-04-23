<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Create a compressed SQL backup of the database';

    public function handle(): int
    {
        $config = DB::connection()->getConfig();
        if (($config['driver'] ?? null) !== 'mysql') {
            $this->error('Only MySQL is supported at this time.');
            return self::FAILURE;
        }
        Storage::disk('local')->makeDirectory('backups');
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql.gz';
        $localPath = storage_path('app/backups/' . $filename);
        $host = escapeshellarg($config['host'] ?? '127.0.0.1');
        $port = escapeshellarg($config['port'] ?? '3306');
        $database = escapeshellarg($config['database']);
        $username = escapeshellarg($config['username']);
        $password = $config['password'] ?? '';
        if (!empty($password)) {
            $command = sprintf('MYSQL_PWD=%s mysqldump --host=%s --port=%s --user=%s --single-transaction --routines --triggers %s | gzip > %s', escapeshellarg($password), $host, $port, $username, $database, escapeshellarg($localPath));
        } else {
            $command = sprintf('mysqldump --host=%s --port=%s --user=%s --single-transaction --routines --triggers %s | gzip > %s', $host, $port, $username, $database, escapeshellarg($localPath));
        }
        exec($command, $output, $returnCode);
        if ($returnCode !== 0) {
            $this->error('mysqldump failed with code ' . $returnCode);
            return self::FAILURE;
        }
        $this->info('Backup created: ' . $filename);
        $this->pruneOldBackups(30);
        return self::SUCCESS;
    }

    private function pruneOldBackups(int $keep): void
    {
        $files = Storage::disk('local')->files('backups');
        $files = array_filter($files, fn($f) => str_ends_with($f, '.sql.gz') || str_ends_with($f, '.sql'));
        rsort($files);
        $toDelete = array_slice($files, $keep);
        foreach ($toDelete as $file) {
            Storage::disk('local')->delete($file);
            $this->line('Pruned old backup: ' . basename($file));
        }
    }
}
