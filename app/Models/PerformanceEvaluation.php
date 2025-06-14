<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceEvaluation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'guard_user_id',
        'supervisor_user_id',
        'evaluation_date',
        'evaluation_period',
        'overall_score',
        'supervisor_comments',
        'guard_feedback',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'evaluation_date' => 'date',
        'overall_score' => 'decimal:2',
    ];

    /**
     * Get the guard being evaluated.
     */
    public function guard()
    {
        return $this->belongsTo(User::class, 'guard_user_id');
    }

    /**
     * Get the supervisor who conducted the evaluation.
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_user_id');
    }

    /**
     * Get the detailed ratings for each criterion in this evaluation.
     */
    public function ratings()
    {
        return $this->hasMany(EvaluationRating::class);
    }
}
