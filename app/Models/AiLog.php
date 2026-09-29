<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLog extends Model
{
    protected $fillable = [
        'report_id',
        'model',
        'prompt',
        'response',
        'status',
        'error',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}