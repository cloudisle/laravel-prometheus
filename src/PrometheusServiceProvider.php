<?php

namespace CloudIsle\Prometheus;

use CloudIsle\Prometheus\Http\MetricEndpoint;
use Exception;
use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricsRegistrationException;

class PrometheusServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     * @throws Exception
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/prometheus.php', 'prometheus'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../config/metrics.php', 'metrics'
        );

        $this->app->singleton(CollectorRegistry::class, function () {
            $registry = $this->createCollectorRegistry();
            MetricRegistrar::register($registry);
            return $registry;
        });

        $this->app->singleton(PrometheusService::class, fn ($app) => new PrometheusService($app->make(CollectorRegistry::class)));
        $this->app->singleton(SimpleMetricsService::class, fn ($app) => new SimpleMetricsService($app->make(PrometheusService::class)));
        $this->app->singleton(MetricEndpoint::class, fn ($app) => new MetricEndpoint($app->make(PrometheusService::class)));
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Publish configs
        $this->publishes([
            __DIR__.'/../config/prometheus.php' => config_path('prometheus.php'),
            __DIR__.'/../config/metrics.php' => config_path('metrics.php'),
        ], 'config');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    /**
     * @throws Exception
     */
    protected function createCollectorRegistry(): CollectorRegistry
    {
        $driver = config('prometheus.driver');

        return CollectorRegistryFactory::create($driver);
    }

}
