<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingModule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'training_category_id',
        'title_ar',
        'description_ar',
        'content_type',
        'content_url',
        'text_content_ar',
        'duration_minutes',
        'difficulty_level',
        'is_mandatory',
        'order_in_category',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that this module belongs to.
     */
    public function category()
    {
        return $this->belongsTo(TrainingCategory::class, 'training_category_id');
    }
}
