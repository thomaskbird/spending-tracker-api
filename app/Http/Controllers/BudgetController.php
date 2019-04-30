<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Models\Budget;

class BudgetController extends Controller {
    public function budget_create() {

    }

    public function budget_edit() {

    }

    public function budget_remove($id) {

    }

    public function budget_single($id) {
        $budget = Budget::find($id);
        return response(json_encode($budget));
    }

    public function view($start, $end) {
        $end = $end .' 23:59:59';
        $transactions = Transaction::with('recurring')->whereRaw('occurred_at > ? AND occurred_at < ?', [$start, $end])->orderBy('occurred_at', 'desc')->get();

        return response(json_encode($transactions));
    }

    public function budget_list(Request $request) {
        $user_id = $this->getUserIdFromToken($request->bearerToken());
        $budgets = Budget::where('user_id', $user_id)->get();
        return response(json_encode($budgets));
    }
}