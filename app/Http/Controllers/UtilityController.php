<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UtilityController extends Controller {
    public function single_model_update($model, $target_id, $key, $val) {
        $model = ucfirst($model);
        $target_model = \App\Http\Models\$model::find($target_id);

        $target_model->$key = $val;

        $target_model->save();

        return response(json_encode([
            'status' => true,
            ucfirst($model) => $target_model
        ]));
    }
}