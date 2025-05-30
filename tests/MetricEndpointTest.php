<?php

namespace CloudIsle\Prometheus\Tests;

use CloudIsle\Prometheus\Http\MetricEndpoint;
use CloudIsle\Prometheus\PrometheusService;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase;
use Illuminate\Http\Response;
use Mockery;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;

class MetricEndpointTest extends TestCase
{

    use MockeryPHPUnitIntegration;

    public function test_metrics_are_returned_in_text_format()
    {
        $registry = new CollectorRegistry(new InMemory());
        $registry->getOrRegisterCounter('', 'test_counter', 'Test Counter', ['label'])->inc(['label' => 'value']);
        $registry->getOrRegisterGauge('', 'test_gauge', 'Test Gauge', ['label'])->set(42, ['label' => 'value']);

        $metrics = $registry->getMetricFamilySamples();

        $mockService = Mockery::mock(PrometheusService::class);
        $mockService->shouldReceive('metrics')->once()->andReturn($metrics);

        // Use partial mock to inject our mock renderer
        $endpoint = new MetricEndpoint($mockService);

        $response = $endpoint();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(RenderTextFormat::MIME_TYPE, $response->headers->get('Content-Type'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('test_counter{label="value"} 1', $response->getContent());
        $this->assertStringContainsString('test_gauge{label="value"} 42', $response->getContent());
    }

}

