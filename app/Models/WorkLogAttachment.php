<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkLogAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_log_id',
        'original_name',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    protected $appends = ['file_size_human', 'download_url'];

    public function workLog(): BelongsTo
    {
        return $this->belongsTo(WorkLog::class);
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes === 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('work-logs.download-attachment', $this->id);
    }
}