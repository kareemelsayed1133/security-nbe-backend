<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceCheck extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'security_device_id',
        'checked_by_user_id',
        'check_time',
        'status_reported',
        'notes',
        'image_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_time' => 'datetime',
    ];

    /**
     * Get the security device that was checked.
     */
    public function securityDevice()
    {
        return $this->belongsTo(SecurityDevice::class);
    }

    /**
     * Get the user (guard) who performed the check.
     */
    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'checked_by_user_id');
    }
}
