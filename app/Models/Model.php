<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Info;

class Model extends Info
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'double',
    ];

    protected $hidden = [];
}
