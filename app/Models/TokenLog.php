<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenLog extends Model
{
    protected $fillable = [
        'tokens',
        'action',
    ];
}
