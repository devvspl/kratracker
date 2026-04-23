<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    /**
     * List all backup files.
     */
    public function index()
    {
        $backups = $this->getBackupFiles();

        return view('profile.edit', [
            'user'    => auth()->user(),
            'backups' => $backups,
        ]);
    }

    /**
     * Trigger a manual backup now.
     */
    public function create(Request $request)
    {
        try {
            \Artisan::call('db:backup');
            return redirect()->route('profile.edit', ['tab' => 'backup'])
                ->with('backup_status', 'Backup created successfully.');
        } catch (\Exception $e) {
            return redirect()->route('profile.edit', ['tab' => 'backup'])
                ->with('backup_error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Download a specific backup file.
     */
    public function download(string $filename): StreamedResponse
    {
        // Sanitize filename to prevent path traversal
        $filename = basename($filename);
        $path     = 'backups/' . $filename;

        if (! Storage::disk('local')->exists($path)) {
            abort(404, 'Backup file not found.');
        }

        return Storage::disk('local')->download($path, $filename);
    }

    /**
     * Delete a specific backup file.
     */
    public function destroy(string $filename)
    {
        $filename = basename($filename);
        $path     = 'backups/' . $filename;

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        return redirect()->route('profile.edit', ['tab' => 'backup'])
            ->with('backup_status', 'Backup deleted.');
    }

    /**
     * Get a sorted list of backup files with metadata.
     */
    private function getBackupFiles(): array
    {
        $files = Storage::disk('local')->files('backups');

        $backups = [];

        foreach ($files as $file) {
            if (! str_ends_with($file, '.sql') && ! str_ends_with($file, '.sql.gz')) {
                continue;
            }

            $name     = basename($file);
            $size     = Storage::disk('local')->size($file);
            $modified = Storage::disk('local')->lastModified($file);

            $backups[] = [
                'name'     => $name,
                'size'     => $this->formatBytes($size),
                'created'  => \Carbon\Carbon::createFromTimestamp($modified)->format('d M Y, H:i'),
                'timestamp' => $modified,
            ];
        }

        // Sort newest first
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}