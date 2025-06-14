<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'icon_class',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
