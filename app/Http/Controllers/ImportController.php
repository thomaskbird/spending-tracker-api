<?php namespace App\Http\Controllers;

use App\Http\Models\Import;
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

        $this->extract_and_insert_rows($import);

//        return response(json_encode([
//            'status' => true,
//            'data' => [
//                'import' => $import
//            ]
//        ]));
    }

    private function extract_and_insert_rows($import) {
        echo storage_path('imports') .' - '. $import->filename; exit;
        $file = fopen(storage_path('imports'));
    }

    private function extract_rows() {

    }
}