<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftChangeRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shift_id',
        'requesting_user_id',
        'requested_start_time',
        'requested_end_time',
        'requested_day_off_date',
        'request_type',
        'reason',
        'status',
        'processed_by_user_id',
        'supervisor_notes',
        'processed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requested_start_time' => 'datetime',
        'requested_end_time' => 'datetime',
        'requested_day_off_date' => 'date',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the original shift for the request.
     */
    public function originalShift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    /**
     * Get the user who submitted the request.
     */
    public function requestingUser()
    {
        return $this->belongsTo(User::class, 'requesting_user_id');
    }

    /**
     * Get the user who processed the request.
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}
