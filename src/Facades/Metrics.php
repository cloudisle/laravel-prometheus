<?php

namespace CloudIsle\Prometheus\Facades;

use CloudIsle\Prometheus\PrometheusService;
use CloudIsle\Prometheus\SimpleMetricsService;
use Illuminate\Support\Facades\Facade;

/**
 * class Metrics
 * @package CloudIsle\Prometheus
 * @method static counter(string $namespace, string $name, string $help, array $labels = [])
 * @method static gauge(string $namespace, string $name, string $help, array $labels = [])
 * @method static histogram(string $namespace, string $name, string $help, array $labels = [], ?array $buckets = null)
 * @method static summary(string $namespace, string $name, string $help, array $labels = [], int $maxAgeSeconds = 600, ?array $quantiles = null)
 */
class Metrics extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return SimpleMetricsService::class;
    }

}