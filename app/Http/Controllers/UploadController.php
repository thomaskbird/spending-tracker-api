<?php namespace App\Http\Controllers;

use App\Http\Models\User;

use Illuminate\Http\Request;

class UploadController extends Controller {
    public function upload_file(Request $request, $type = false) {
        $file = $request->file('image');
        $filename = $file->getClientOriginalName();
        $fully_qualified_path = public_path('img');
        $file->move($fully_qualified_path, $filename);

        $user_id = $this->getUserIdFromToken($request->bearerToken());

        $user = User::find($user_id);
        $user->profile = '/img/'. $filename;
        $user->save();

        return response(json_encode([
            'status' => true,
            'data' => [
                'user' => $user
            ]
        ]));
    }
}