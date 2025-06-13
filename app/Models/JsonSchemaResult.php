<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JsonSchemaResult extends Model
{
    protected $fillable = [
        'url',
        'schema',
    ];
}
