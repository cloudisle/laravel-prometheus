<?php

use CloudIsle\Prometheus\Http\MetricEndpoint;
use CloudIsle\Prometheus\Http\Middleware\PrometheusBasicAuth;
use Illuminate\Support\Facades\Route;

Route::get(config('prometheus.endpoint', '/metrics'), function () {
    return app(MetricEndpoint::class)();
})->middleware(PrometheusBasicAuth::class);