<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $fillable = [
        'name',
        'code',
        'active',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}