<?php

namespace App\Http\Controllers;

use App\Http\Models\Taggable;
use Illuminate\Http\Request;
use Validator;
use DB;

use App\Http\Models\Tag;

class TagController extends Controller {
    public function action_create(Request $request) {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required'
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

            if(isset($input['taggable_id'])) {
                $transaction_tag = Taggable::create([
                    'taggable_id' => $input['taggable_id'],
                    'tag_id' => $tag->id,
                    'taggable_type' => $input['taggable_type']
                ]);

                return response(json_encode([
                    'data' => [
                        'tag' => $tag,
                        'transaction_tag' => $transaction_tag
                    ]
                ]));
            }

            return response(json_encode([
                'status' => true,
                'data' => [
                    'tag' => $tag
                ]
            ]));
        }
    }

    public function action_edit(Request $request, $id) {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required'
        ]);

        if($validator->fails()) {
            return response(json_encode([
                'status' => false,
                'errors' => $validator->errors()
            ]));
        } else {
            $tag = Tag::find($id);
            $input['slug'] = $this->create_slug($input['title']);

            foreach($input as $key => $val) {
                $tag->$key = $val;
            }

            $tag->save();

            return response(json_encode([
                'status' => true,
                'data' => [
                    'tag' => $tag
                ]
            ]));
        }
    }

    public function action_remove($id) {
        $tag = Tag::find($id);
        $tag->delete();

        $taggables = Taggable::where('tag_id', $id);

        if($taggables) {
            $taggables->delete();
        }

        return response(json_encode([
            'status'=> true
        ]));
    }

    public function single($id) {
        $tag = Tag::with('transactions')->find($id);
        return response(json_encode([
            'status' => true,
            'data' => [
                'tag' => $tag
            ]
        ]));
    }

    public function view(Request $request, $start, $end) {
        $user_id = $this->getUserIdFromToken($request->bearerToken());
        $start = $start .' 00:00:00';
        $end = $end .' 23:59:59';

        // todo: this query never returns any transactions
        $tags = Tag::where('user_id', $user_id)->with(['transactions' => function($query) use ($start, $end) {
            $query
                ->whereRaw(
                    'occurred_at >= ? AND occurred_at <= ?',
                    [$start, $end]
                )
                ->groupBy(DB::raw('YEAR(occurred_at) DESC, MONTH(occurred_at) DESC'));
        }])->get();

        return response(json_encode([
            'status'=> true,
            'data' => [
                'tags' => $tags
            ]
        ]));
    }
}