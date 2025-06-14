<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiAssistantDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'mime_type',
        'file_size_bytes',
        'display_name_ar',
        'description_ar',
        'uploaded_by_user_id',
        'last_indexed_at',
        'is_active',
    ];

    protected $casts = [
        'last_indexed_at' => 'datetime',
        'is_active' => 'boolean',
        'file_size_bytes' => 'integer',
    ];

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
