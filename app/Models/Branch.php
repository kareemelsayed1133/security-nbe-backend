<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'geofence_radius_meters',
    ];

    /**
     * Get the users associated with the branch.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Get the incidents that occurred in this branch.
     */
    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Get the security devices in this branch.
     */
    public function securityDevices()
    {
        return $this->hasMany(SecurityDevice::class);
    }

    /**
     * Get the assets in this branch.
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
