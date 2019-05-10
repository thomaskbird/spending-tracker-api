<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Models\Budget;

class BudgetController extends Controller {
    public function budget_create(Request $request) {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required',
            'icon' => 'required',
            'amount' => 'required'
        ]);

        if($validator->fails()) {
            return response(json_encode([
                'status' => false,
                'errors' => $validator->errors()
            ]), 401);
        } else {
            $user_id = $this->getUserIdFromToken($request->bearerToken());
            $input['user_id'] = $user_id;

            $budget = Budget::create($input);

            return response(json_encode([
                'status' => true,
                'data' => [
                    'budget' => $budget
                ]
            ]));
        }
    }

    public function budget_edit(Request $request, $id) {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required',
            'icon' => 'required',
            'amount' => 'required'
        ]);

        if($validator->fails()) {
            return response(json_encode([
                'status' => false,
                'errors' => $validator->errors()
            ]), 401);
        } else {
            $budget = Budget::find($id);

            foreach($input as $key => $val) {
                $budget->$key = $val;
            }

            $budget->save();

            return response(json_encode([
                'status' => true,
                'data' => [
                    'budget' => $budget
                ]
            ]));
        }
    }

    public function budget_remove($id) {
        $budget = Budget::find($id);
        $budget->delete();

        return response(json_encode([
            'status' => true,
            'data' => [
                'deleted_id' => $id
            ]
        ]));
    }

    public function budget_single(Request $request, $id) {
        $user_id = $this->getUserIdFromToken($request->bearerToken());
        $budget = Budget::find($id);
        $budget_transactions = $this->budget_tag_transactions($id, $user_id);

        if($budget->user_id === $user_id) {
            return response(json_encode([
                'status' => true,
                'data' => [
                    'budget' => $budget,
                    'budget_transactions' => $budget_transactions
                ]
            ]));
        } else {
            return response(json_encode([
                'status' => false,
                'errors' => [
                    'You are unauthorized to view this resource'
                ]
            ]));
        }
    }

    public function budget_list(Request $request) {
        $user_id = $this->getUserIdFromToken($request->bearerToken());

        $budgets = Budget::with(['tags' => function($query) {
            $query->with('transactions');
        }])->where('user_id', $user_id)->get();

        return response(json_encode([
            'status' => true,
            'data' => [
                'budgets' => $budgets
            ]
        ]));
    }

    public function budget_tag_transactions($id, $user_id) {
        $transactions = [];
        $budget = Budget::with(['tags' => function($query) {
            $query->with('transactions');
        }])->whereRaw('id = ? AND user_id = ?', [$id, $user_id])->first();

        foreach($budget->tags as $tag) {
            foreach($tag->transactions as $transaction) {
                array_push($transactions, $transaction);
            }
        }

        return $transactions;
    }
}