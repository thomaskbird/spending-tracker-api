<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model {
    protected $fillable = [
        'created_by', 'title', 'description'
    ];
}