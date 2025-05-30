<?php

namespace CloudIsle\Prometheus\Tests;

use CloudIsle\Prometheus\PrometheusService;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\Summary;
use Mockery;

class PrometheusServiceTest extends TestCase
{

    use MockeryPHPUnitIntegration;

    public function test_counter_registers_and_returns_counter()
    {
        $mockRegistry = Mockery::mock(CollectorRegistry::class);
        $mockCounter = Mockery::mock(Counter::class);
        $mockRegistry->shouldReceive('getOrRegisterCounter')
            ->once()
            ->with('ns', 'name', 'help', ['label' => 'value'])
            ->andReturn($mockCounter);
        $service = new PrometheusService($mockRegistry);
        $result = $service->counter('ns', 'name', 'help', ['label' => 'value']);
        $this->assertSame($mockCounter, $result);
    }

    public function test_gauge_registers_and_returns_gauge()
    {
        $mockRegistry = Mockery::mock(CollectorRegistry::class);
        $mockGauge = Mockery::mock(Gauge::class);
        $mockRegistry->shouldReceive('getOrRegisterGauge')
            ->once()
            ->with('ns', 'name', 'help', ['label' => 'value'])
            ->andReturn($mockGauge);
        $service = new PrometheusService($mockRegistry);
        $result = $service->gauge('ns', 'name', 'help', ['label' => 'value']);
        $this->assertSame($mockGauge, $result);
    }

    public function test_histogram_registers_and_returns_histogram()
    {
        $mockRegistry = Mockery::mock(CollectorRegistry::class);
        $mockHistogram = Mockery::mock(Histogram::class);
        $mockRegistry->shouldReceive('getOrRegisterHistogram')
            ->once()
            ->with('ns', 'name', 'help', ['label' => 'value'], [0.1, 1, 5])
            ->andReturn($mockHistogram);
        $service = new PrometheusService($mockRegistry);
        $result = $service->histogram('ns', 'name', 'help', ['label' => 'value'], [0.1, 1, 5]);
        $this->assertSame($mockHistogram, $result);
    }

    public function test_summary_registers_and_returns_summary()
    {
        $mockRegistry = Mockery::mock(CollectorRegistry::class);
        $mockSummary = Mockery::mock(Summary::class);
        $mockRegistry->shouldReceive('getOrRegisterSummary')
            ->once()
            ->with('ns', 'name', 'help', ['label' => 'value'], 600, null)
            ->andReturn($mockSummary);
        $service = new PrometheusService($mockRegistry);
        $result = $service->summary('ns', 'name', 'help', ['label' => 'value'], 600, null);
        $this->assertSame($mockSummary, $result);
    }

    public function test_metrics_returns_metric_family_samples()
    {
        $mockRegistry = Mockery::mock(CollectorRegistry::class);
        $expected = ['sample1', 'sample2'];
        $mockRegistry->shouldReceive('getMetricFamilySamples')
            ->once()
            ->andReturn($expected);
        $service = new PrometheusService($mockRegistry);
        $result = $service->metrics();
        $this->assertSame($expected, $result);
    }

}

