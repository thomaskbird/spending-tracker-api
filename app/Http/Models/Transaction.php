<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {
    protected $fillable = [
        'user_id', 'import_id', 'recurring_id', 'title', 'description', 'amount', 'type', 'is_bill', 'status', 'queued', 'occurred_at'
    ];

    public function recurring() {
        return $this->belongsTo('App\Http\Models\Recurring', 'recurring_id');
    }

    public function tags() {
        return $this->morphToMany(
            'App\Http\Models\Tag',
            'taggable'
        );
    }

    public function import() {
        return $this->hasOne('App\Http\Models\Import', 'id', 'import_id');
    }
}