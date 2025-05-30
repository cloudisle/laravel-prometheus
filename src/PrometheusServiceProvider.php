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
            $this->createCollectorRegistry();
        });

        $this->app->singleton(PrometheusService::class, PrometheusService::class);
        $this->app->singleton(SimpleMetricsService::class, SimpleMetricsService::class);
        $this->app->singleton(MetricEndpoint::class, MetricEndpoint::class);

        $this->registerDefaultMetrics();
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

    /**
     * @throws MetricsRegistrationException
     */
    protected function registerDefaultMetrics(): void
    {
        MetricRegistrar::register();
    }

}
