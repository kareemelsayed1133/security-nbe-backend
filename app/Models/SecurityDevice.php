<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityDevice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_id',
        'device_type_id',
        'name',
        'serial_number',
        'location_description',
        'qr_code_identifier',
        'status',
        'last_checked_at',
        'last_checked_by_user_id',
        'next_maintenance_due',
        'installation_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_checked_at' => 'datetime',
        'next_maintenance_due' => 'date',
        'installation_date' => 'date',
    ];

    /**
     * Get the branch where the device is located.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the type of the device.
     */
    public function deviceType()
    {
        return $this->belongsTo(DeviceType::class);
    }

    /**
     * Get the user who last checked this device.
     */
    public function lastCheckedBy()
    {
        return $this->belongsTo(User::class, 'last_checked_by_user_id');
    }

    /**
     * Get the check history for this device.
     */
    public function deviceChecks()
    {
        return $this->hasMany(DeviceCheck::class);
    }

    /**
     * Get the maintenance requests for this device.
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
