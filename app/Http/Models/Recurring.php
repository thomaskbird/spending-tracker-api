<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Recurring extends Model {
    protected $fillable = [
        'user_id', 'recurring_type', 'start_at', 'end_at'
    ];

    public function transactions() {
        return $this->hasMany('App\Http\Models\Transaction');
    }
}