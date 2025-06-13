<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoContent extends Model
{
    protected $fillable = [
        'name',
        'url',
        'seo_description',
        'seo_title',
        'seo_meta_description',
        'check_result',
    ];
}
