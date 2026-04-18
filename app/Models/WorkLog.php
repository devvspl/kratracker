<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkLog extends Model
{
    protected $fillable = [
        'user_id', 'sub_kra_id', 'application_id', 'title', 'description',
        'log_date', 'priority_id', 'status_id', 'achievement_value',
        'target_value_snapshot', 'score_calculated', 'logic_applied',
        'total_duration', 'actual_duration', 'duration_difference',
        'test_status', 'testing_details', 'remark', 'attachments',
    ];

    protected $casts = [
        'log_date'             => 'date',
        'achievement_value'    => 'decimal:2',
        'target_value_snapshot'=> 'decimal:2',
        'score_calculated'     => 'decimal:2',
        'duration_difference'  => 'decimal:2',
        'total_duration'       => 'decimal:2',
        'actual_duration'      => 'decimal:2',
        'attachments'          => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subKra()
    {
        return $this->belongsTo(SubKra::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(WorkLogFeedback::class);
    }

    public function calculateScore()
    {
        $subKra = $this->subKra;
        $logic = $subKra->logic;
        
        if ($logic->scoring_type === 'proportional') {
            // Logic 1: score = (achievement/target) * 100, capped at 100
            $score = $this->target_value_snapshot > 0 
                ? min(($this->achievement_value / $this->target_value_snapshot) * 100, 100)
                : 0;
        } else {
            // Logic 3: binary - 100 if achievement >= target, else 0
            $score = $this->achievement_value >= $this->target_value_snapshot ? 100 : 0;
        }
        
        $this->score_calculated = round($score, 2);
        $this->logic_applied = $logic->name;
        $this->save();
        
        return $this->score_calculated;
    }

    public function getWeightedScoreAttribute()
    {
        return ($this->score_calculated * $this->subKra->weightage) / 100;
    }
}
