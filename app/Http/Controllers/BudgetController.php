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

    public function budget_edit() {

    }

    public function budget_remove($id) {

    }

    public function budget_single(Request $request, $id) {
        $budget = Budget::find($id);
        $user_id = $this->getUserIdFromToken($request->bearerToken());

        if($budget->user_id === $user_id) {
            return response(json_encode($budget));
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
        $budgets = Budget::where('user_id', $user_id)->get();
        return response(json_encode($budgets));
    }
}