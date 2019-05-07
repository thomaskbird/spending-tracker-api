<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionTag extends Model {
    protected $table = "tag_transactions";

    protected $fillable = [
        'tag_id', 'transaction_id'
    ];
}