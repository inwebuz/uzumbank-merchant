<?php

namespace Inwebuz\UzumbankMerchant\Middleware;

use Closure;

class BasicAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $login = config('uzumbankmerchant.auth.login');
        $password = config('uzumbankmerchant.auth.password');

        // Perform basic auth check
        if (empty($login) || empty($password) || $request->getUser() !== $login || $request->getPassword() !== $password) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        return $next($request);
    }
}
