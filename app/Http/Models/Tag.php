<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model {
    protected $fillable = [
        'user_id', 'parent_id', 'title', 'slug', 'description'
    ];

    public function taggable() {
        return $this->morphTo();
    }

    public function budgets() {
        return $this->morphedByMany('App\Http\Models\Budget', 'taggable');
    }

    public function transactions() {
        return $this->morphToMany('App\Http\Models\Transaction', 'taggable', 'taggables', 'tag_id', 'taggable_id');
    }
}