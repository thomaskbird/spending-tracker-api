<?php namespace App\Http\Controllers;

use Illuminate\Support\Str;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getUserIdFromToken($token) {
        $decoded_token = base64_decode($token);
        $token_parts = explode('||', $decoded_token);
        return $token_parts[0];
    }

    public function create_slug($str, $splitter = '-') {
        return Str::slug($str, $splitter);
    }
}
