<?php

namespace CloudIsle\Prometheus\Tests;

use CloudIsle\Prometheus\Http\Middleware\PrometheusBasicAuth;
use Illuminate\Http\Request;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase;

class PrometheusBasicAuthTest extends TestCase
{

    use MockeryPHPUnitIntegration;

    public function test_allows_request_when_auth_disabled()
    {
        $request = Request::create('/metrics', 'GET');
        config(['prometheus.auth.enabled' => false]);
        $middleware = new PrometheusBasicAuth();
        $response = $middleware->handle($request, fn($req) => 'next-called');
        $this->assertEquals('next-called', $response);
    }

    public function test_allows_request_with_valid_credentials()
    {
        $request = Request::create('/metrics', 'GET');
        $_SERVER['PHP_AUTH_USER'] = 'user';
        $_SERVER['PHP_AUTH_PW'] = 'pass';
        config([
            'prometheus.auth.enabled' => true,
            'prometheus.auth.username' => 'user',
            'prometheus.auth.password' => 'pass',
        ]);
        $middleware = new PrometheusBasicAuth();
        $response = $middleware->handle($request, fn($req) => 'next-called');
        $this->assertEquals('next-called', $response);
    }

    public function test_denies_request_with_invalid_credentials()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $request = Request::create('/metrics', 'GET');
        $_SERVER['PHP_AUTH_USER'] = 'baduser';
        $_SERVER['PHP_AUTH_PW'] = 'badpass';
        config([
            'prometheus.auth.enabled' => true,
            'prometheus.auth.username' => 'user',
            'prometheus.auth.password' => 'pass',
        ]);
        $middleware = new PrometheusBasicAuth();
        $middleware->handle($request, fn($req) => 'next-called');
    }

}

