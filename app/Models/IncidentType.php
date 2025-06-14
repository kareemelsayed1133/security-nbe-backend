<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name_ar',
        'icon_class',
        'color_code',
        'description',
    ];

    /**
     * Get the incidents of this type.
     */
    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }
}
