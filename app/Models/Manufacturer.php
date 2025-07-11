<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manufacturer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'contact_name',
        'phone',
        'email',
        'address',
        'website',
        'country',
        'is_active'
    ];

    protected $dates = ['deleted_at'];  // Indicates 'deleted_at' is a date column

    public function medicines()
    {
        return $this->hasMany(Medicine::class);
    }
}
