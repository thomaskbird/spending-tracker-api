<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Taggable extends Model {
    protected $fillable = [
        'tag_id', 'taggable_id', 'taggable_type'
    ];
}