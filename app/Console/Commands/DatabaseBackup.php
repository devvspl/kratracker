<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature   = 'db:backup';
    protected $description = 'Create a SQL backup of the database using PHP PDO (no mysqldump required)';

    public function handle(): int
    {
        $config = DB::connection()->getConfig();
        $driver = $config['driver'] ?? 'unknown';

        Storage::disk('local')->makeDirectory('backups');

        $filename  = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $localPath = storage_path('app' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . $filename);

        // Ensure directory exists
        if (!is_dir(dirname($localPath))) {
            mkdir(dirname($localPath), 0755, true);
        }

        try {
            if ($driver === 'mysql') {
                $this->dumpMysql($config, $localPath);
            } elseif ($driver === 'sqlite') {
                $this->dumpSqlite($config, $localPath);
            } else {
                $this->error("Unsupported driver: {$driver}");
                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            // Remove empty file if created
            if (file_exists($localPath) && filesize($localPath) === 0) {
                @unlink($localPath);
            }
            return self::FAILURE;
        }

        $size = file_exists($localPath) ? filesize($localPath) : 0;
        if ($size === 0) {
            $this->error('Backup file is empty — something went wrong.');
            @unlink($localPath);
            return self::FAILURE;
        }

        $this->info("Backup created: {$filename} (" . $this->formatBytes($size) . ')');
        $this->pruneOldBackups(30);

        return self::SUCCESS;
    }

    // ─── MySQL via PDO ────────────────────────────────────────────────────────

    private function dumpMysql(array $config, string $localPath): void
    {
        $host     = $config['host']     ?? '127.0.0.1';
        $port     = $config['port']     ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        $pdo = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        $sql  = "-- Performia Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: {$database}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        // Get all tables
        $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $sql .= $this->dumpMysqlTable($pdo, $table);
        }

        $sql .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($localPath, $sql);
    }

    private function dumpMysqlTable(\PDO $pdo, string $table): string
    {
        $quotedTable = "`{$table}`";
        $sql = "-- Table: {$table}\n";
        $sql .= "DROP TABLE IF EXISTS {$quotedTable};\n";

        // CREATE TABLE statement
        $createRow = $pdo->query("SHOW CREATE TABLE {$quotedTable}")->fetch(\PDO::FETCH_ASSOC);
        $sql .= ($createRow['Create Table'] ?? '') . ";\n\n";

        // Row data
        $rows = $pdo->query("SELECT * FROM {$quotedTable}")->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $columns = '`' . implode('`, `', array_keys($rows[0])) . '`';
            $sql .= "INSERT INTO {$quotedTable} ({$columns}) VALUES\n";

            $valueLines = [];
            foreach ($rows as $row) {
                $values = array_map(function ($val) use ($pdo) {
                    if ($val === null) return 'NULL';
                    return $pdo->quote((string) $val);
                }, array_values($row));
                $valueLines[] = '(' . implode(', ', $values) . ')';
            }
            $sql .= implode(",\n", $valueLines) . ";\n\n";
        }

        return $sql;
    }

    // ─── SQLite via file copy ─────────────────────────────────────────────────

    private function dumpSqlite(array $config, string $localPath): void
    {
        $dbFile = $config['database'];

        if (!file_exists($dbFile)) {
            throw new \RuntimeException("SQLite file not found: {$dbFile}");
        }

        // For SQLite we export as SQL using PDO
        $pdo    = new \PDO("sqlite:{$dbFile}");
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $sql  = "-- Performia SQLite Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "PRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n\n";

        $tables = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($tables as $tableInfo) {
            $table = $tableInfo['name'];
            $sql  .= "-- Table: {$table}\n";
            $sql  .= "DROP TABLE IF EXISTS \"{$table}\";\n";
            $sql  .= $tableInfo['sql'] . ";\n\n";

            $rows = $pdo->query("SELECT * FROM \"{$table}\"")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $cols   = '"' . implode('", "', array_keys($row)) . '"';
                $values = array_map(function ($val) use ($pdo) {
                    if ($val === null) return 'NULL';
                    return $pdo->quote((string) $val);
                }, array_values($row));
                $sql .= "INSERT INTO \"{$table}\" ({$cols}) VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "COMMIT;\nPRAGMA foreign_keys=ON;\n";

        file_put_contents($localPath, $sql);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function pruneOldBackups(int $keep): void
    {
        $files = Storage::disk('local')->files('backups');
        $files = array_values(array_filter($files, fn($f) =>
            str_ends_with($f, '.sql') || str_ends_with($f, '.sql.gz')
        ));
        rsort($files);

        foreach (array_slice($files, $keep) as $file) {
            Storage::disk('local')->delete($file);
            $this->line('Pruned: ' . basename($file));
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}
