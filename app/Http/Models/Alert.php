<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model {
    protected $fillable = [
        'user_id', 'budget_id', 'threshold'
    ];
}