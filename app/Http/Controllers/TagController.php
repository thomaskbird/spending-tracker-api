<?php

namespace App\Http\Controllers;

use App\Http\Models\Tag;

class TagController extends Controller {
    public function action_create() {

    }

    public function single($id) {
        $tag = Tag::find($id);
        return response(json_encode($tag));
    }

    public function view() {
        $tags = Tag::paginate(10);
        return response(json_encode($tags));
    }
}