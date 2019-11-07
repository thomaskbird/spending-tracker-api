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

        $import = Import::create([
            'user_id' => $user_id,
            'type' => $input['sourceType'],
            'filename' => $filename,
            'records' => 0,
            'record_ids' => ''
        ]);

        return response(json_encode([
            'status' => true,
            'data' => [
                'import' => $import
            ]
        ]));
    }
}