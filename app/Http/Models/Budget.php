<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model {
    protected $fillable = [
        'user_id', 'title', 'slug', 'description', 'amount'
    ];
}