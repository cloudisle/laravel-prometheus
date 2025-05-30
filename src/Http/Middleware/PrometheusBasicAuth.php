<?php

namespace CloudIsle\Prometheus\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PrometheusBasicAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (config('prometheus.auth.enabled', false)) {
            $this->authenticate(
                config('prometheus.auth.username'),
                config('prometheus.auth.password')
            );
        }

        return $next($request);
    }

    protected function authenticate(string $username, string $password): void
    {
        $hasAuth = isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        if (!$hasAuth || $_SERVER['PHP_AUTH_USER'] !== $username || $_SERVER['PHP_AUTH_PW'] !== $password) {
            header('WWW-Authenticate: Basic realm="Prometheus Metrics"');
            abort(401, 'Unauthorized');
        }
    }
}

