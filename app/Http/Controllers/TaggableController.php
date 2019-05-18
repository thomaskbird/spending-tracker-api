<?php namespace App\Http\Controllers;

use App\Http\Models\Tag;
use App\Http\Models\Taggable;

use App\Http\Models\Transaction;
use Illuminate\Http\Request;
use Validator;

class TaggableController extends Controller {

    public function get_tags_with_selected_status(Request $request) {
        $input = $request->all();
        $tags_formatted = [];
        $user_id = $this->getUserIdFromToken($request->bearerToken());

        $tags = Tag::where('user_id', $user_id)->get()->toArray();
        $relation_ids = Taggable::whereRaw('taggable_id = ? AND taggable_type = ?', [$input['taggable_id'], $input['taggable_type']])->pluck('tag_id')->toArray();

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
            'taggable_id' => 'required',
            'tag_id' => 'required'
        ]);

        if($validator->fails()) {
            return response(json_encode([
                'status' => false,
                'errors' => $validator->errors()
            ]), 401);
        } else {

            $remove = Taggable::whereRaw('taggable_id = ? AND tag_id = ? AND taggable_type = ?', [
                $input['taggable_id'],
                $input['tag_id'],
                $input['taggable_type']
            ])->delete();

            if($remove) {
                if($input['taggable_type'] === 'App\Http\Models\Transaction') {
                    $transaction = Transaction::find($input['taggable_id']);

                    if($transaction->recurring_id) {
                        $recurring_transaction_ids = Transaction::where('recurring_id', $transaction->recurring_id)->pluck('id')->toArray();
                        Taggable::whereRaw('taggable_type = ? AND tag_id = ?', [$input['taggable_type'], $input['tag_id']])->whereIn('taggable_id', $recurring_transaction_ids)->delete();
                    }
                }

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
            'taggable_id' => 'required',
            'tag_id' => 'required',
            'taggable_type' => 'required'
        ]);

        if($validator->fails()) {
            return response(json_encode([
                'status' => false,
                'errors' => $validator->errors()
            ]), 401);
        } else {
            $existing = Taggable::whereRaw('taggable_id = ? AND tag_id = ? AND taggable_type = ?', [$input['taggable_id'], $input['tag_id'], $input['taggable_type']])->first();

            if($existing) {
                return response(json_encode([
                    'status' => false,
                    'errors' => ['This tag `'. $input['tag_id'] .'` already exists for the target with the id: `'. $input['taggable_id'] .'`']
                ]), 401);
            } else {
                $recurring_transactions = [];
                if($input['taggable_type'] === 'App\Http\Models\Transaction') {
                    $transaction = Transaction::find($input['taggable_id']);
                    if($transaction->recurring_id) {
                        $transactions = Transaction::where('recurring_id', $transaction->recurring_id)->get();
                        foreach($transactions as $trans) {
                            $generated_recurring_tag = $this->tag_relation_add_recurring($trans->id, $input['tag_id']);
                            array_push($recurring_transactions, $generated_recurring_tag);
                        }
                    }
                }

                $tag_relation = Taggable::create([
                    'taggable_id' => $input['taggable_id'],
                    'tag_id' => $input['tag_id'],
                    'taggable_type' => $input['taggable_type']
                ]);

                return response(json_encode([
                    'status' => true,
                    'data' => [
                        'tag_relation' => $tag_relation,
                        'tag_recurring_relations' => count($recurring_transactions) ? $recurring_transactions : []
                    ]
                ]));
            }
        }
    }

    public function tag_relation_add_recurring($taggable_id, $tag_id) {
        return Taggable::create([
            'taggable_id' => $taggable_id,
            'tag_id' => $tag_id,
            'taggable_type' => 'App\Http\Models\Transaction'
        ]);
    }
}