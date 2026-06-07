<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    protected $table = 'blacklist';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'reason',
    ];
}
