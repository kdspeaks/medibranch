<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MedicineUnit extends Model
{
    protected $fillable = ['name', 'short_code', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function forms(): BelongsToMany
    {
        return $this->belongsToMany(MedicineForm::class, 'medicine_form_unit');
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
