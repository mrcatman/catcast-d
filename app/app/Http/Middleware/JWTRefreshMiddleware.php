<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
class JWTRefreshMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     *
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        try {
            $token = JWTAuth::parseToken();
            if ($token) {
                $user = JWTAuth::toUser($token);
                if ($user) {
                    $request->merge(['user' => $user]);
                    $request->setUserResolver(function () use ($user) {
                        return $user;
                    });
                    Auth::setUser($user);
                }
            }
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {

        }
        return $next($request);
    }


}
