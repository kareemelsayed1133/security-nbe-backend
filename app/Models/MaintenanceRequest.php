<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'security_device_id',
        'reported_by_user_id',
        'description',
        'priority',
        'status',
        'assigned_to_technician_id',
        'resolution_notes',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the security device for this request.
     */
    public function securityDevice()
    {
        return $this->belongsTo(SecurityDevice::class);
    }

    /**
     * Get the user who reported the issue.
     */
    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    /**
     * Get the technician assigned to this request.
     */
    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_to_technician_id');
    }
}
