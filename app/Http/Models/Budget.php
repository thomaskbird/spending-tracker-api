<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model {
    protected $fillable = [
        'user_id', 'title', 'slug', 'description', 'icon', 'amount'
    ];

    public function tags() {
        return $this->hasMany('App\Http\Models\Tag', 'tag_id');
    }
}