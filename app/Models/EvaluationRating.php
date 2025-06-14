<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationRating extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'performance_evaluation_id',
        'evaluation_criterion_id',
        'score',
        'comments',
    ];

    /**
     * Get the main performance evaluation report.
     */
    public function performanceEvaluation()
    {
        return $this->belongsTo(PerformanceEvaluation::class);
    }

    /**
     * Get the criterion being rated.
     */
    public function criterion()
    {
        return $this->belongsTo(EvaluationCriterion::class, 'evaluation_criterion_id');
    }
}
