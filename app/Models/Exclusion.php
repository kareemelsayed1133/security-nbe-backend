<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exclusion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'national_id',
        'reason',
        'attachment_url',
        'added_by_user_id',
    ];

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }
}
