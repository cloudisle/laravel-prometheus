<?php

namespace CloudIsle\Prometheus\Tests;

use CloudIsle\Prometheus\SimpleMetricsService;
use CloudIsle\Prometheus\PrometheusService;
use CloudIsle\Prometheus\MetricErrorHandler;
use Orchestra\Testbench\TestCase;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\Summary;
use Prometheus\Exception\MetricsRegistrationException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class SimpleMetricsServiceTest extends TestCase
{

    use MockeryPHPUnitIntegration;

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('metrics.labels', ['foo' => 'bar', 'baz' => 'qux']);
    }

    public function test_counter_calls_prometheus_service()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $mock->shouldReceive('counter')
            ->once()
            ->with('', 'test_counter', 'test_counter', ['foo', 'baz'])
            ->andReturn(Mockery::mock(Counter::class));
        $service = new SimpleMetricsService($mock);
        $service->counter('test_counter');
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    public function test_gauge_calls_prometheus_service()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $mock->shouldReceive('gauge')
            ->once()
            ->with('', 'test_gauge', 'test_gauge', ['foo', 'baz'])
            ->andReturn(Mockery::mock(Gauge::class));
        $service = new SimpleMetricsService($mock);
        $service->guage('test_gauge');
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    public function test_histogram_calls_prometheus_service()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $mock->shouldReceive('histogram')
            ->once()
            ->with('', 'test_histogram', 'test_histogram', ['foo', 'baz'], null)
            ->andReturn(Mockery::mock(Histogram::class));
        $service = new SimpleMetricsService($mock);
        $service->histogram('test_histogram');
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    public function test_summary_calls_prometheus_service()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $mock->shouldReceive('summary')
            ->once()
            ->with('', 'test_summary', 'test_summary', ['foo', 'baz'], 600, null)
            ->andReturn(Mockery::mock(Summary::class));
        $service = new SimpleMetricsService($mock);
        $service->summary('test_summary');
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    public function test_labels_are_merged_and_overridden()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $counter = Mockery::mock(Counter::class);
        $mock->shouldReceive('counter')
            ->once()
            ->with('', 'test_counter', 'test_counter', ['foo', 'baz'])
            ->andReturn($counter);
        $counter->shouldReceive('getLabelNames')
            ->once()
            ->andReturn(['foo', 'baz']);
        $counter->shouldReceive('incBy')
            ->once()
            ->with(1, ['override', 'qux']);
        $service = new SimpleMetricsService($mock);
        $service->increment('test_counter', 1, ['foo' => 'override']);
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    public function test_increment_catches_exceptions_and_calls_error_handler()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $mock->shouldReceive('counter')
            ->andThrow(new MetricsRegistrationException('fail'));
        $errorHandler = Mockery::mock(MetricErrorHandler::class);
        $errorHandler->shouldReceive('handle')->once();
        $service = new SimpleMetricsService($mock, $errorHandler);
        $service->increment('test_counter');
        $this->assertTrue(true); // If exception is not thrown, test passes
    }

    public function test_record_catches_exceptions_and_calls_error_handler()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $mock->shouldReceive('guage')
            ->andThrow(new MetricsRegistrationException('fail'));
        $errorHandler = Mockery::mock(MetricErrorHandler::class);
        $errorHandler->shouldReceive('handle')->once();
        $service = new SimpleMetricsService($mock, $errorHandler);
        $service->record('test_gauge');
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    public function test_observe_catches_exceptions_and_calls_error_handler()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $mock->shouldReceive('histogram')
            ->andThrow(new MetricsRegistrationException('fail'));
        $errorHandler = Mockery::mock(MetricErrorHandler::class);
        $errorHandler->shouldReceive('handle')->once();
        $service = new SimpleMetricsService($mock, $errorHandler);
        $service->observe('test_histogram');
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    public function test_summarize_catches_exceptions_and_calls_error_handler()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $mock->shouldReceive('summary')
            ->andThrow(new MetricsRegistrationException('fail'));
        $errorHandler = Mockery::mock(MetricErrorHandler::class);
        $errorHandler->shouldReceive('handle')->once();
        $service = new SimpleMetricsService($mock, $errorHandler);
        $service->summarize('test_summary');
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    public function test_increment_increments_counter()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $counter = Mockery::mock(Counter::class);
        $mock->shouldReceive('counter')
            ->once()
            ->with('', 'test_counter', 'test_counter', ['foo', 'baz'])
            ->andReturn($counter);
        $counter->shouldReceive('getLabelNames')
            ->once()
            ->andReturn(['foo', 'baz']);
        $counter->shouldReceive('incBy')
            ->once()
            ->with(1, ['bar', 'qux']);

        $service = new SimpleMetricsService($mock);
        $service->increment('test_counter');

        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    public function test_record_records_gauge()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $gauge = Mockery::mock(Gauge::class);
        $mock->shouldReceive('gauge')
            ->once()
            ->with('', 'test_gauge', 'test_gauge', ['foo', 'baz'])
            ->andReturn($gauge);
        $gauge->shouldReceive('getLabelNames')
            ->once()
            ->andReturn(['foo', 'baz']);
        $gauge->shouldReceive('set')
            ->once()
            ->with(123, ['bar', 'qux']);

        $service = new SimpleMetricsService($mock);
        $service->record('test_gauge', 123);

        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    public function test_observe_observes_histogram()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $histogram = Mockery::mock(Histogram::class);
        $mock->shouldReceive('histogram')
            ->once()
            ->with('', 'test_histogram', 'test_histogram', ['foo', 'baz'], null)
            ->andReturn($histogram);
        $histogram->shouldReceive('getLabelNames')
            ->once()
            ->andReturn(['foo', 'baz']);
        $histogram->shouldReceive('observe')
            ->once()
            ->with(1.23, ['bar', 'qux']);

        $service = new SimpleMetricsService($mock);
        $service->observe('test_histogram', 1.23);

        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    public function test_summarize_summarizes_summary()
    {
        $mock = Mockery::mock(PrometheusService::class);
        $summary = Mockery::mock(Summary::class);
        $mock->shouldReceive('summary')
            ->once()
            ->with('', 'test_summary', 'test_summary', ['foo', 'baz'], 600, null)
            ->andReturn($summary);
        $summary->shouldReceive('getLabelNames')
            ->once()
            ->andReturn(['foo', 'baz']);
        $summary->shouldReceive('observe')
            ->once()
            ->with(4.56, ['bar', 'qux']);

        $service = new SimpleMetricsService($mock);
        $service->summarize('test_summary', 4.56);

        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

}
