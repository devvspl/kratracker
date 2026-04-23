<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkLog extends Model
{
    protected $fillable = [
        'user_id', 'sub_kra_id', 'application_id', 'module_id', 'title', 'description',
        'log_date', 'priority_id', 'status_id', 'achievement_value',
        'target_value_snapshot', 'score_calculated', 'logic_applied',
        'start_time', 'end_time', 'total_duration', 'actual_duration', 'duration_difference',
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

    public function module()
    {
        return $this->belongsTo(ApplicationModule::class, 'module_id');
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

    public function attachments()
    {
        return $this->hasMany(WorkLogAttachment::class);
    }

    public function links()
    {
        return $this->hasMany(WorkLogLink::class);
    }

    public function calculateScore()
    {
        $subKra = $this->subKra;
        $logic  = $subKra->logic;

        // ── 1. Base score from KRA logic ──────────────────────────────────────
        if ($logic->scoring_type === 'proportional') {
            $base = $this->target_value_snapshot > 0
                ? min(($this->achievement_value / $this->target_value_snapshot) * 100, 100)
                : 0;
        } else {
            // binary
            $base = $this->achievement_value >= $this->target_value_snapshot ? 100 : 0;
        }

        // ── 2. Status multiplier ──────────────────────────────────────────────
        $statusName = optional($this->status)->name ?? '';
        $statusMultiplier = match(true) {
            str_contains($statusName, 'Completed')   => 1.0,
            str_contains($statusName, 'In Progress') => 0.7,
            str_contains($statusName, 'On Hold')     => 0.4,
            default                                  => 0.0, // Not Started, Cancelled
        };
        $score = $base * $statusMultiplier;

        // ── 3. Priority bonus (only if task has meaningful progress) ──────────
        if ($statusMultiplier > 0) {
            $priorityName = optional($this->priority)->name ?? '';
            $score += match($priorityName) {
                'High', 'Critical' => 10,
                'Medium' => 5,
                default => 0,
            };

            // ── 4. Test status bonus ──────────────────────────────────────────
            $score += match($this->test_status) {
                'Passed'  => 5,
                'Failed'  => -10,
                default   => 0,
            };

            // ── 5. Duration efficiency bonus ─────────────────────────────────
            $total  = (float) ($this->total_duration  ?? 0);
            $actual = (float) ($this->actual_duration ?? 0);
            if ($total > 0 && $actual > 0) {
                if ($actual <= $total) {
                    $score += 5;  // finished on time or early
                } elseif ($actual > $total * 1.2) {
                    $score -= 5;  // took >20% longer than planned
                }
            }

            // ── 6. Feedback rating bonus ──────────────────────────────────────
            $feedbacks = $this->feedbacks;
            if ($feedbacks->isNotEmpty()) {
                $avgRating = $feedbacks->avg('rating');
                $score += match(true) {
                    $avgRating >= 4.5 => 10,
                    $avgRating >= 3.5 => 5,
                    $avgRating >= 2.5 => 0,
                    default           => -5,
                };
            }
        }

        // ── 7. Clamp 0–100 ────────────────────────────────────────────────────
        $this->score_calculated = round(max(0, min(100, $score)), 2);
        $this->logic_applied    = $logic->name;
        $this->save();

        return $this->score_calculated;
    }

    public function getWeightedScoreAttribute()
    {
        return ($this->score_calculated * $this->subKra->weightage) / 100;
    }
}
