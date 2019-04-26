<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Models\User;

use Illuminate\Support\Facades\Mail;
use Hash;
use Validator;

class CredentialController extends Controller {
    public function action_login() {

    }

    public function action_signup(Request $request) {
        $input = $request->except('_token');

        $validator = Validator::make($input, [
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return response(json_encode([
                'status' => false,
                'errors' => $validator->errors()
            ]), 401);
        } else {
            $salt = $this->generateRandomString();
            $activation_code = base64_encode($input['email'] .'||'. config('general.site_salt') .'||'. $salt);

            $input['salt'] = $salt;
            $input['password'] = Hash::make($input['password']);
            $input['activation_code'] = $activation_code;

            $user = User::create($input);
            $email = $user->email;

            Mail::send('emails.basic', $input, function($message) use ( $email ){

                $message->from( 'info@SpendingTracker.com', 'SpendingTracker' );
                $message->to($email)->subject('Account Activation');

            });

            return response(json_encode([
                'status' => true,
                'data' => [
                    'user' => $user
                ]
            ]));
        }
    }

    public function account_user_activate(Request $request, $activation_code) {
        $input = $request->except('_token');

        $validator = Validator::make($input, [
            'first_name' => 'required',
            'last_name' => 'required'
        ]);

        if($validator->fails()) {
            return response(json_encode([
                'status' => false,
                'errors' => $validator->errors()
            ]), 401);
        } else {
            $activation_code = base64_decode($activation_code);
            $activation_code_parts = explode('||', $activation_code);

            $user = User::where('email', $activation_code_parts[0])->first();
            $generated_code = base64_encode($activation_code_parts[0] .'||'. config('general.site_salt') .'||'. $activation_code_parts[2]);

            if($generated_code === $user->activation_code) {
                $user->status = 'active';
                $user->first_name = $input['first_name'];
                $user->last_name = $input['last_name'];
                $user->save();

                return response(json_encode([
                    'status' => true,
                    'data' => [
                        'user' => $user
                    ]
                ]));
            } else {
                return response(json_encode([
                    'status' => false,
                    'errors' => ['Uh oh something went wrong please try again or contact an administrator']
                ]), 401);
            }
        }
    }

    public function recovery_password() {

    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}