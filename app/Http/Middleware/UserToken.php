<?php namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Closure;

use App\Http\Models\User;

class UserToken extends Controller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $token = $request->bearerToken();

        if($token) {
            $decrypt = base64_decode($token);
            $parts = explode('||', $decrypt);

            $user = User::find($parts[0]);

            if($user->api_token === $token && $this->timestamp() < $parts[1]) {
                $request->user_id = $parts[0];
                return $next($request);
            } else {
                return response(['Errors' => ['You don\'t appear to be logged in, please login and try again']], 401);
            }
        } else {
            return response(['Errors' => ['The request is missing the token']], 401);
        }
    }

    private function timestamp() {
        return date('Y-m-d G:i:s');
    }
}
