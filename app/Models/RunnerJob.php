<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RunnerJob extends Model
{
    protected $fillable = ['class', 'method', 'parameters', 'status', 'error_message', 'retry_count', 'pid'];

    protected $casts = [
        'parameters' => 'array',
    ];
}
