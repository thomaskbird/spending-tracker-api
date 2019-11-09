<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
            $input['slug'] = $this->create_slug($input['title']);

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

            $budget->slug = $this->create_slug($input['title']);

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

    public function budget_single(Request $request, $id, $start, $end) {
        $user_id = $this->getUserIdFromToken($request->bearerToken());
        $budget = Budget::find($id);

        $start = $start .' 00:00:00';
        $end = $end .' 23:59:59';

        $budget_transactions = $this->budget_tag_transactions($id, $user_id, $start, $end);

        if($budget->user_id == $user_id) {
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

    public function budgets_list(Request $request) {
        $user_id = $this->getUserIdFromToken($request->bearerToken());

        $budgets = Budget::where('user_id', $user_id)->get();

        return response(json_encode([
            'status' => true,
            'data' => [
                'budgets' => $budgets
            ]
        ]));
    }

    public function budgets_list_with_alerts(Request $request) {
        $user_id = $this->getUserIdFromToken($request->bearerToken());

        $budgets = Budget::with('alert')->where('user_id', $user_id)->get();

        return response(json_encode([
            'status' => true,
            'data' => [
                'budgets' => $budgets
            ]
        ]));
    }

    public function budget_list_with_transactions(Request $request, $start, $end) {
        $user_id = $this->getUserIdFromToken($request->bearerToken());
        $start = $start .' 00:00:00';
        $end = $end .' 23:59:59';

        $budgets = Budget::with(['tags' => function($query) use ($user_id, $start, $end) {
            // need to add a filter for only the current month
            $query->with(['transactions' => function($query) use ($user_id, $start, $end) {
                $query->whereRaw(
                    'user_id = ? AND occurred_at >= ? AND occurred_at <= ?',
                    [$user_id, $start, $end]
                );
            }]);
        }])->where('user_id', $user_id)->get();

        return response(json_encode([
            'status' => true,
            'data' => [
                'budgets' => $budgets
            ]
        ]));
    }

    public function budget_tag_transactions($id, $user_id, $start, $end) { 
	    $transactions = [];
        $budget = Budget::with(['tags' => function($query) use ($start, $end) {
            $query->with(['transactions' => function($transaction_query) use($start, $end) {
                $transaction_query->whereRaw(
                    'occurred_at >= ? AND occurred_at <= ?',
                    [$start, $end]
                );
            }]);
        }])->whereRaw('id = ? AND user_id = ?', [$id, $user_id])->first();

        foreach($budget->tags as $tag) {
            foreach($tag->transactions as $transaction) {
                array_push($transactions, $transaction);
            }
        }

        return $transactions;
    }

    public function visualization_budgets(Request $request, $start, $end) {
        $user_id = $this->getUserIdFromToken($request->bearerToken());
        $start = $start .' 00:00:00';
        $end = $end .' 23:59:59';
        $data = [];

        $budgets = Budget::with(['tags' => function($query) use ($user_id, $start, $end) {
            $query->with(['transactions' => function($query) use ($user_id, $start, $end) {
                $query->whereRaw(
                    'user_id = ? AND occurred_at >= ? AND occurred_at <= ?',
                    [$user_id, $start, $end]
                );
            }]);
        }])->where('user_id', $user_id)->get()->toArray();

        foreach($budgets as $budget) {
            $amount = 0;

            if(count($budget['tags']) !== 0) {
                foreach($budget['tags'] as $tag) {
                    if(count($tag['transactions']) !== 0) {
                        foreach($tag['transactions'] as $transaction) {
                            if($transaction['type'] === 'expense') {
                                $amount = $amount + $transaction['amount'];
                            } else {
                                $amount = $amount - $transaction['amount'];
                            }
                        }
                    }
                }
            }

            array_push($data, [
                'id' => $budget['id'],
                'name' => $budget['title'],
                'limit' => $budget['amount'],
                'current' => $amount
            ]);
        }

        return response(json_encode([
            'status' => true,
            'data' => [
                'budgets' => $data
            ]
        ]));
    }

    public function visualization_budget(Request $request, $id, $months) {
        $user_id = $this->getUserIdFromToken($request->bearerToken());

        // todo: determine start and end range

        $now = Carbon::now();
        $start = $now->subtract($months, 'month');
        $end = $now->endOfMonth();

        return [
            'id' => $id,
            'months' => $months,
            'now' => $now,
            'start' => $start,
            'end' => $end
        ];


        $budgets = Budget::with(['tags' => function($query) use ($user_id, $start, $end) {
            $query->with(['transactions' => function($query) use ($user_id, $start, $end) {
                $query->whereRaw(
                    'user_id = ? AND occurred_at >= ? AND occurred_at <= ?',
                    [$user_id, $start, $end]
                );
            }]);
        }])->find('id', $id)->toArray();
    }
}
