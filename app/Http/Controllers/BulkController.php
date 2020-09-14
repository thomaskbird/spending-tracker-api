<?php namespace App\Http\Controllers;

use App\Http\Models\Transaction;

use Illuminate\Http\Request;

class BulkController extends Controller {

    public function bulk_transaction_remove(Request $request) {
        $transaction_ids = $request->input('transactionIds');
        $deleted = Transaction::whereIn('id', $transaction_ids)->delete();

        if($deleted) {
            return response(json_encode([
                'status' => true,
                'data' => [
                    'transactionIds' => $transaction_ids
                ]
            ]));
        } else {
            return response(json_encode([
                'status' => false,
                'errors' => [
                    'Something went wrong please try again!'
                ]
            ]));
        }
    }
}