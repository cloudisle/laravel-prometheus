<?php

namespace CloudIsle\Prometheus\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase;
use CloudIsle\Prometheus\PrometheusServiceProvider;
use CloudIsle\Prometheus\PrometheusService;
use CloudIsle\Prometheus\SimpleMetricsService;
use CloudIsle\Prometheus\Http\MetricEndpoint;

class PrometheusServiceProviderTest extends TestCase
{

    use MockeryPHPUnitIntegration;

    protected function getPackageProviders($app): array
    {
        $this->getEnvironmentSetUp($app);

        return [PrometheusServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('prometheus.driver', 'memory');
    }

    public function test_services_are_registered()
    {
        $this->assertTrue($this->app->bound(PrometheusService::class));
        $this->assertTrue($this->app->bound(SimpleMetricsService::class));
        $this->assertTrue($this->app->bound(MetricEndpoint::class));
    }

}


