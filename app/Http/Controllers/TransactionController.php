<?php

namespace App\Http\Controllers;

use App\Http\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller {
    public function action_create(Request $request) {

    }

    public function single($id) {
        $transaction = Transaction::find($id);
        return response(json_encode($transaction));
    }

    public function view() {
        $transactions = Transaction::paginate(10);
        return response(json_encode($transactions));
    }
}