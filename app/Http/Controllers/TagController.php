<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Models\Tag;

class TagController extends Controller {
    public function action_create(Request $request) {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required|unique:tags'
        ]);

        if($validator->fails()) {
            return response(json_encode([
                'status' => false,
                'errors' => $validator->errors()
            ]));
        } else {
            $user_id = $this->getUserIdFromToken($request->bearerToken());
            $input['user_id'] = $user_id;
            $tag = Tag::create($input);
            return response(json_encode($tag));
        }
    }

    public function single($id) {
        $tag = Tag::find($id);
        return response(json_encode($tag));
    }

    public function view(Request $request) {
        $user_id = $this->getUserIdFromToken($request->bearerToken());
        $tags = Tag::where('user_id', $user_id)->get();
        return response(json_encode($tags));
    }
}