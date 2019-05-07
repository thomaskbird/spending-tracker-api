<?php

namespace App\Http\Controllers;

use App\Http\Models\TagRelation;
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
            $input['slug'] = $this->create_slug($input['title']);
            $tag = Tag::create($input);

            if(isset($input['target_id'])) {
                $transaction_tag = TagRelation::create([
                    'target_id' => $input['target_id'],
                    'tag_id' => $tag->id,
                    'type' => $input['type']
                ]);

                return response(json_encode([
                    'data' => [
                        'tag' => $tag,
                        'transaction_tag' => $transaction_tag
                    ]
                ]));
            }

            return response(json_encode([
                'data' => [
                    'tag' => $tag
                ]
            ]));
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