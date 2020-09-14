<?php namespace App\Http\Controllers;

use App\Http\Models\Transaction;

use Illuminate\Http\Request;

class BulkController extends Controller {

    public function bulk_transaction_remove(Request $request) {
        $transaction_ids = $request->input['transactionIds'];

        return $transaction_ids;
    }
}