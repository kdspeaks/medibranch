<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'gst_number',
        'drug_license_number',
        'phone',
        'email',
        'is_active',
        'taxable',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'taxable' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
