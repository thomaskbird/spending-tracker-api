<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {
    protected $fillable = [
        'recurring_id', 'submitted_by', 'title', 'description', 'amount', 'type', 'status', 'occurred_at'
    ];

    public function recurring() {
        return $this->belongsTo('App\Http\Models\Recurring', 'recurring_id');
    }
}