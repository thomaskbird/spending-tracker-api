<?php namespace App\Http\Controllers;

use App\Http\Models\Import;
use App\Http\Models\Transaction;
use App\Http\Models\User;

use Validator;
use Illuminate\Http\Request;

class ImportController extends Controller {

    public function action_import(Request $request) {
        $input = $request->all();

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $fully_qualified_path = storage_path('imports');
        $file->move($fully_qualified_path, $filename);

        $user_id = $this->getUserIdFromToken($request->bearerToken());

        // todo: check if first line is header
        // todo: iterate through file and create transactions
        // todo: count the number of total transactions imported
        // todo: record all of the transactions ids created.
        // todo: modify transactions table to track what is in queue and what has been fully imported.

        $import = Import::create([
            'user_id' => $user_id,
            'type' => $input['sourceType'],
            'filename' => $filename,
            'records' => 0,
            'record_ids' => ''
        ]);

        $insertions = $this->extract_and_insert_rows($import, $user_id);

        $import->records = $insertions['records'];
        $import->record_ids = $insertions['record_ids'];

        $import->save();

        return response(json_encode([
            'status' => true,
            'data' => [
                'import' => $import
            ]
        ]));
    }

    private function extract_and_insert_rows($import, $user_id) {
        $index = 0;
        ini_set('auto_detect_line_endings',TRUE);
        $handle = fopen(storage_path('imports') .'/'. $import->filename, 'r');
        $transaction_ids = '';

        while ( ($data = fgetcsv($handle) ) !== FALSE ) {
            if($index !== 0) {
                $reformatted_timestamp = date('Y-m-d h:i:s', strtotime($data[0]));
                $amount_formatted = floatval($data[3]);

                if(substr_count($data[3], '-') === 0) {
                    $transaction_data = [
                        'user_id' => $user_id,
                        'title' => substr($data[2], 0, 20),
                        'description' => $data[2],
                        'amount' => $amount_formatted,
                        'type' => 'income',
                        'status' => 'queued',
                        'occurred_at' => $reformatted_timestamp
                    ];
                } else {
                    $transaction_data = [
                        'user_id' => $user_id,
                        'title' => substr($data[2], 0, 20),
                        'description' => $data[2],
                        'amount' => $amount_formatted,
                        'type' => 'expense',
                        'status' => 'queued',
                        'occurred_at' => $reformatted_timestamp
                    ];
                }

                $transaction = Transaction::create($transaction_data);
                $transaction_ids = $transaction_ids .', '. $transaction->id;
            }

            $index++;
        }
        ini_set('auto_detect_line_endings',FALSE);

        return [
            'record_ids' => ltrim($transaction_ids, ', '),
            'records' => $index - 1,
        ];
    }
}