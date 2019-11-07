<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Import extends Model {
    protected $fillable = [
        'user_id', 'type', 'filename', 'records', 'record_ids'
    ];
}