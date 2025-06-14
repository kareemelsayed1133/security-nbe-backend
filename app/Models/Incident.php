<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_number',
        'reported_by_user_id',
        'supervisor_assigned_id',
        'branch_id',
        'incident_type_id',
        'description',
        'location_text',
        'latitude',
        'longitude',
        'severity',
        'status',
        'reported_at',
        'resolved_at',
        'resolution_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the user who reported the incident.
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    /**
     * Get the supervisor assigned to the incident.
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_assigned_id');
    }

    /**
     * Get the branch where the incident occurred.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the type of the incident.
     */
    public function incidentType()
    {
        return $this->belongsTo(IncidentType::class);
    }

    /**
     * Get all media associated with the incident.
     */
    public function media()
    {
        return $this->hasMany(IncidentMedia::class);
    }

    /**
     * Get the updates log for this incident.
     */
    public function updates()
    {
        return $this->hasMany(IncidentUpdate::class)->latest();
    }
}
