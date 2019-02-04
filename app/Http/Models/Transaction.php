<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {
    protected $fillable = [
        'name', 'slug', 'description', 'amount', 'type'
    ];
}