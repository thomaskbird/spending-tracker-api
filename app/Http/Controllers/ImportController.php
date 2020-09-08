<?php namespace App\Http\Controllers;

use App\Http\Models\Import;
use App\Http\Models\Transaction;
use App\Http\Models\User;

use Validator;
use Illuminate\Http\Request;

class ImportController extends Controller {

    public function imports_list(Request $request) {
        $user_id = $this->getUserIdFromToken($request->bearerToken());
        $imports = Import::where('user_id', $user_id)->orderBy('created_at', 'desc')->get();

        return response(json_encode([
            'status' => true,
            'data' => [
                'imports' => $imports
            ]
        ]));
    }

    public function imports_single($id) {
        $import = Import::find($id);

        return response(json_encode([
            'status' => true,
            'data' => [
                'import' => $import
            ]
        ]));
    }

    public function action_import(Request $request) {
        $input = $request->all();

        $source_type = $input['sourceType'];
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $fully_qualified_path = storage_path('imports');
        $file->move($fully_qualified_path, $filename);

        $user_id = $this->getUserIdFromToken($request->bearerToken());

        $import = Import::create([
            'user_id' => $user_id,
            'type' => $input['sourceType'],
            'filename' => $filename,
            'records' => 0,
            'record_ids' => ''
        ]);

        $insertions = $this->extract_and_insert_rows($import, $user_id, $source_type);

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

    private function extract_and_insert_rows($import, $user_id, $source_type) {
        $index = 0;
        ini_set('auto_detect_line_endings',TRUE);
        $handle = fopen(storage_path('imports') .'/'. $import->filename, 'r');
        $transaction_ids = '';

        while ( ($data = fgetcsv($handle) ) !== FALSE ) {
            if($index !== 0) {

                switch($source_type) {
                    case 'discover':
                        $transaction_data = $this->map_discover($data, $user_id);
                    break;
                    case 'chase':
                        $transaction_data = $this->map_chase($data, $user_id);
                    break;
                    case 'fifth-third-checking':
                        $transaction_data = $this->map_fifth_third_checking($data, $user_id);
                    break;
                    case 'fifth-third-credit':
                        $transaction_data = $this->map_fifth_third_credit($data, $user_id);
                        break;
                    case 'capital-one':
                        $transaction_data = $this->map_captial_one($data, $user_id);
                    break;
                }

                $transaction_data['import_id'] = $import->id;

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

    private function map_captial_one($data, $user_id) {
        $reformatted_occured_timestamp = date('Y-m-d h:i:s', strtotime($data[0]));
        $reformatted_posted_timestamp = date('Y-m-d h:i:s', strtotime($data[1]));

        if($data[5] !== '' && !empty($data[5])) {
            $amount_formatted = floatval(str_replace('-', '', $data[5]));
        } else {
            $amount_formatted = floatval(str_replace('-', '', $data[6]));
        }

        if($data[6] !== '' && !empty($data[6])) {
            $transaction_data = [
                'user_id' => $user_id,
                'title' => substr($data[3], 0, 20),
                'description' => $data[3] .' | '. $data[4],
                'amount' => $amount_formatted,
                'type' => 'income',
                'status' => 'queued',
                'created_at' => $reformatted_occured_timestamp,
                'occurred_at' => $reformatted_posted_timestamp
            ];
        } else {
            $transaction_data = [
                'user_id' => $user_id,
                'title' => substr($data[3], 0, 20),
                'description' => $data[3] .' | '. $data[4],
                'amount' => $amount_formatted,
                'type' => 'expense',
                'status' => 'queued',
                'created_at' => $reformatted_occured_timestamp,
                'occurred_at' => $reformatted_posted_timestamp
            ];
        }

        return $transaction_data;
    }

    private function map_fifth_third_credit($data, $user_id) {
        $reformatted_timestamp = date('Y-m-d h:i:s', strtotime($data[0]));
        $amount_formatted = floatval(str_replace('-', '', $data[2]));

        if(substr_count($data[2], '-') === 0) {
            $transaction_data = [
                'user_id' => $user_id,
                'title' => substr($data[1], 0, 20),
                'description' => $data[1],
                'amount' => $amount_formatted,
                'type' => 'income',
                'status' => 'queued',
                'occurred_at' => $reformatted_timestamp
            ];
        } else {
            $transaction_data = [
                'user_id' => $user_id,
                'title' => substr($data[1], 0, 20),
                'description' => $data[1],
                'amount' => $amount_formatted,
                'type' => 'expense',
                'status' => 'queued',
                'occurred_at' => $reformatted_timestamp
            ];
        }

        return $transaction_data;
    }

    private function map_fifth_third_checking($data, $user_id) {
        $reformatted_timestamp = date('Y-m-d h:i:s', strtotime($data[0]));
        $amount_formatted = floatval(str_replace('-', '', $data[3]));

        if(substr_count($data[3], '-') === 0) {
            $transaction_data = [
                'user_id' => $user_id,
                'title' => substr($data[1], 0, 20),
                'description' => $data[1],
                'amount' => $amount_formatted,
                'type' => 'income',
                'status' => 'queued',
                'occurred_at' => $reformatted_timestamp
            ];
        } else {
            $transaction_data = [
                'user_id' => $user_id,
                'title' => substr($data[1], 0, 20),
                'description' => $data[1],
                'amount' => $amount_formatted,
                'type' => 'expense',
                'status' => 'queued',
                'occurred_at' => $reformatted_timestamp
            ];
        }

        return $transaction_data;
    }

    private function map_chase($data, $user_id) {
        $reformatted_timestamp = date('Y-m-d h:i:s', strtotime($data[1]));
        $amount_formatted = floatval(str_replace('-', '', $data[3]));

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

        return $transaction_data;
    }

    private function map_discover($data, $user_id) {
        $reformatted_timestamp = date('Y-m-d h:i:s', strtotime($data[0]));
        $amount_formatted = floatval(str_replace('-', '', $data[3]));

        if($data[2] === 'INTERNET PAYMENT - THANK YOU') {
            $transaction_data = [
                'user_id' => $user_id,
                'title' => substr($data[2], 0, 20),
                'description' => $data[2],
                'amount' => $amount_formatted,
                'type' => 'expense',
                'status' => 'queued',
                'occurred_at' => $reformatted_timestamp
            ];
        } else {
            if(substr_count($data[3], '-') === 0) {
                $transaction_data = [
                    'user_id' => $user_id,
                    'title' => substr($data[2], 0, 20),
                    'description' => $data[2],
                    'amount' => $amount_formatted,
                    'type' => 'expense',
                    'status' => 'queued',
                    'occurred_at' => $reformatted_timestamp
                ];
            } else {
                $transaction_data = [
                    'user_id' => $user_id,
                    'title' => substr($data[2], 0, 20),
                    'description' => $data[2],
                    'amount' => $amount_formatted,
                    'type' => 'income',
                    'status' => 'queued',
                    'occurred_at' => $reformatted_timestamp
                ];
            }
        }

        return $transaction_data;
    }
}