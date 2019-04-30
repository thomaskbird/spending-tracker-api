<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model {
    protected $fillable = [
        'user_id', 'parent_id', 'title', 'slug', 'description'
    ];

    public function transactions() {
        return $this->hasMany('App\Http\Models\Transaction', 'transaction_id');
    }
}