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