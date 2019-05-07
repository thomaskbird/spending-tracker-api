<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class TagRelation extends Model {
    protected $table = "tag_relations";

    protected $fillable = [
        'tag_id', 'target_id', 'type'
    ];
}