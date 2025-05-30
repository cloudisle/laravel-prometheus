<?php

namespace CloudIsle\Prometheus\Tests;

use CloudIsle\Prometheus\MetricRegistrar;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase;
use Prometheus\CollectorRegistry;
use Mockery;

class MetricRegistrarTest extends TestCase
{

    use MockeryPHPUnitIntegration;

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('metrics', [
            'namespace' => 'test_ns',
            'labels' => ['foo' => 'bar'],
            'counters' => [
                ['name' => 'counter1', 'help' => 'Counter 1', 'labels' => ['baz']],
            ],
            'gauges' => [
                ['name' => 'gauge1', 'help' => 'Gauge 1', 'labels' => []],
            ],
            'histograms' => [
                ['name' => 'hist1', 'help' => 'Hist 1', 'labels' => [], 'buckets' => [0.1, 1, 5]],
            ],
            'summaries' => [
                ['name' => 'sum1', 'help' => 'Sum 1', 'labels' => [], 'maxAgeSeconds' => 123, 'quantiles' => [0.5, 0.9]],
            ],
        ]);
    }

    public function test_metrics_are_registered()
    {
        $mockRegistry = Mockery::mock(CollectorRegistry::class);
        $mockRegistry->shouldReceive('registerCounter')
            ->once()
            ->with('test_ns', 'counter1', 'Counter 1', ['foo', 'baz']);
        $mockRegistry->shouldReceive('registerGauge')
            ->once()
            ->with('test_ns', 'gauge1', 'Gauge 1', ['foo']);
        $mockRegistry->shouldReceive('registerHistogram')
            ->once()
            ->with('test_ns', 'hist1', 'Hist 1', ['foo'], [0.1, 1, 5]);
        $mockRegistry->shouldReceive('registerSummary')
            ->once()
            ->with('test_ns', 'sum1', 'Sum 1', ['foo'], 123, [0.5, 0.9]);
        MetricRegistrar::register($mockRegistry);
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

}

