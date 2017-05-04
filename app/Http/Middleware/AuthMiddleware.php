<?php

namespace App\Http\Middleware;

use App\Http\Response\Status;
use Closure;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Factory as Auth;

class AuthMiddleware
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    protected $authHeader;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;

        $this->authHeader = [
            'WWW-Authenticate' => 'Basic realm=' . env('BASIC_REALM')
        ];
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (!$request->header('php-auth-user')) {
            return Controller::responseCode(Status::API_LOSE_TOKEN, $this->authHeader);
        }

        if ($this->auth->guest()) {
            return Controller::responseCode(Status::API_FAULT_TOKEN);
        }

        return $next($request);
    }
}
