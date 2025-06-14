<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_type_id',
        'branch_id',
        'name',
        'identifier',
        'status',
        'current_user_id',
        'notes',
    ];

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function currentUser()
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    public function logs()
    {
        return $this->hasMany(AssetLog::class);
    }
}
