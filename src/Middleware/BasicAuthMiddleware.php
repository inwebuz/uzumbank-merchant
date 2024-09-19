<?php

namespace Inwebuz\UzumbankMerchant\Middleware;

use Closure;
use Inwebuz\UzumbankMerchant\UzumbankMerchant;

class BasicAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $login = config('uzumbankmerchant.auth.login');
        $password = config('uzumbankmerchant.auth.password');

        // Perform basic auth check
        if (empty($login) || empty($password) || $request->getUser() !== $login || $request->getPassword() !== $password) {
            return response()->json([
                'serviceId' => $request->input('serviceId'),
                'timestamp' => $request->input('timestamp'),
                'status' => 'FAILED',
                'errorCode' => UzumbankMerchant::ERROR_AUTH_FAILED,
            ], 401);
        }

        return $next($request);
    }
}
