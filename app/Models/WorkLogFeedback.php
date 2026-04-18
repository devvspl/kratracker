<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkLogFeedback extends Model
{
    protected $table = 'work_log_feedbacks';

    protected $fillable = [
        'work_log_id',
        'user_id',
        'feedback_type',
        'comment',
        'rating',
    ];

    public function workLog()
    {
        return $this->belongsTo(WorkLog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
