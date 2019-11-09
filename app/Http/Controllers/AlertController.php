<?php namespace App\Http\Controllers;

use App\Http\Models\Alert;

use Validator;
use Illuminate\Http\Request;

class AlertController extends Controller {

    public function create_alert(Request $request) {
        $input = $request->except('_token');
        $user_id = $this->getUserIdFromToken($request->bearerToken());

        $validator = Validator::make($input, [
            'threshold' => 'required',
            'budget_id' => 'required'
        ]);

        if($validator->fails()) {
            return response(json_encode([
                'status' => false,
                'errors' => $validator->errors()
            ]), 401);
        } else {
            $alert = Alert::create([
                'user_id' => $user_id,
                'budget_id' => $input['budget_id'],
                'threshold' => $input['threshold']
            ]);

            return response(json_encode([
                'status' => true,
                'data' => [
                    'alert' => $alert
                ]
            ]));
        }
    }

    public function remove_alert($id) {
        $alert = Alert::find($id);

        if($alert) {
            $alert->delete();

            return response(json_encode([
                'status' => true,
                'data' => [
                    'msgs' => [
                        'Alert deleted'
                    ]
                ]
            ]));
        } else {
            return response(json_encode([
                'status' => false,
                'data' => [
                    'msgs' => [
                        'Alert could not be found, please try again'
                    ]
                ]
            ]));
        }
    }
}