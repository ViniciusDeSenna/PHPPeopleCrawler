<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    protected $table = 'people';
    protected $fillable = [
        'profile_img_link',
        'profile_img_path',
        'name',
        'nick',
        'mail',
    ];
}
