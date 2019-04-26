<?php

namespace App\Http\Controllers;

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

    public function budget_list() {
        $budgets = Budget::paginate(10);
        return response(json_encode($budgets));
    }
}