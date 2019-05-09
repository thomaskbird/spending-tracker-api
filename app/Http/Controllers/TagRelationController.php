<?php namespace App\Http\Controllers;

use App\Http\Models\Tag;
use App\Http\Models\TagRelation;

use Illuminate\Http\Request;
use Validator;

class TagRelationController extends Controller {

    public function get_tags_with_selected_status(Request $request, $type, $relation_id) {
        $tags_formatted = [];
        $user_id = $this->getUserIdFromToken($request->bearerToken());

        $tags = Tag::where('user_id', $user_id)->get()->toArray();
        $relation_ids = TagRelation::whereRaw('target_id = ? AND type = ?', [$relation_id, $type])->pluck('tag_id')->toArray();

        foreach($tags as $tag) {
            if(in_array($tag['id'], $relation_ids)) {
                $tag['selected'] = true;
            } else {
                $tag['selected'] = false;
            }

            array_push($tags_formatted, $tag);
        }


        return response(json_encode([
            'status' => true,
            'data' => [
                'tags' => $tags_formatted
            ]
        ]));
    }

    public function tag_relation_remove(Request $request) {
        $input = $request->all();

        $validator = Validator::make($input, [
            'target_id' => 'required',
            'tag_id' => 'required'
        ]);

        if($validator->fails()) {
            return response(json_encode([
                'status' => false,
                'errors' => $validator->errors()
            ]), 401);
        } else {
            $remove = TagRelation::whereRaw('target_id = ? AND tag_id = ? AND type = ?', [$input['target_id'], $input['tag_id'], $input['type']])->first();
            if($remove) {
                $remove->delete();
                return response(json_encode(['status' => true]));
            } else {
                return response(json_encode([
                    'status' => false,
                    'errors' => ['No tag relation found']
                ]));
            }
        }
    }

    public function tag_relation_add(Request $request) {
        $input = $request->all();

        $validator = Validator::make($input, [
            'target_id' => 'required',
            'tag_id' => 'required',
            'type' => 'required'
        ]);

        if($validator->fails()) {
            return response(json_encode([
                'status' => false,
                'errors' => $validator->errors()
            ]), 401);
        } else {
            $existing = TagRelation::whereRaw('target_id = ? AND tag_id = ? AND type = ?', [$input['target_id'], $input['tag_id'], $input['type']])->first();

            if($existing) {
                return response(json_encode([
                    'status' => false,
                    'errors' => ['This tag `'. $input['tag_id'] .'` already exists for the target with the id: `'. $input['target_id'] .'`']
                ]), 401);
            } else {
                $tag_relation = TagRelation::create([
                    'target_id' => $input['target_id'],
                    'tag_id' => $input['tag_id'],
                    'type' => $input['type']
                ]);

                return response(json_encode([
                    'status' => true,
                    'data' => [
                        'tag_relation' => $tag_relation
                    ]
                ]));
            }
        }
    }
}