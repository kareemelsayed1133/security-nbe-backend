<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone_number',
        'password',
        'role_id',
        'branch_id',
        'employee_id',
        'annual_leave_balance',
        'profile_image_url',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'annual_leave_balance' => 'integer',
    ];

    /**
     * Get the role that the user belongs to.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the branch that the user belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the incidents reported by the user.
     */
    public function reportedIncidents()
    {
        return $this->hasMany(Incident::class, 'reported_by_user_id');
    }
    
    /**
     * Get the shifts assigned to the user (if they are a guard).
     */
    public function shifts()
    {
        return $this->hasMany(Shift::class, 'user_id');
    }

    /**
     * Get the chat rooms this user is a participant in.
     */
    public function chatRooms()
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_participants');
    }
}
