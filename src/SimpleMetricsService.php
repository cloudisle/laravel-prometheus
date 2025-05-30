<?php

namespace CloudIsle\Prometheus;

use Prometheus\Collector;
use Prometheus\Counter;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\Summary;
use Throwable;

class SimpleMetricsService
{

    private PrometheusService $prometheus;
    private MetricErrorHandler $errorHandler;
    private array $labels;

    public function __construct(PrometheusService $prometheus, ?MetricErrorHandler $errorHandler = null)
    {
        $this->prometheus = $prometheus;
        $this->errorHandler = $errorHandler ?? new LogMetricErrorHandler();

        $this->labels = config('metrics.labels', []);
    }

    /**
     * @throws MetricsRegistrationException
     */
    public function counter(string $name, array $labels = []): Counter
    {
        return $this->prometheus->counter('', $name, $name, $this->labelNames($labels));
    }

    /**
     * @throws MetricsRegistrationException
     */
    public function guage(string $name, array $labels = []): Gauge
    {
        return $this->prometheus->gauge('', $name, $name, $this->labelNames($labels));
    }

    /**
     * @throws MetricsRegistrationException
     */
    public function histogram(string $name, array $labels = [], ?array $buckets = null): Histogram
    {
        return $this->prometheus->histogram('', $name, $name, $this->labelNames($labels), $buckets);
    }

    /**
     * @throws MetricsRegistrationException
     */
    public function summary(string $name, array $labels = [], int $maxAgeSeconds = 600, ?array $quantiles = null): Summary
    {
        return $this->prometheus->summary('', $name, $name, $this->labelNames($labels), $maxAgeSeconds, $quantiles);
    }

    public function increment(string $name, int $value = 1, array $labels = []): void
    {
        $this->graceful($name, function () use ($name, $labels, $value) {
            $counter = $this->counter($name, $labels);
            $counter->incBy($value, $this->labelValues($counter, $labels));
        });
    }

    public function record(string $name, float $value = 0, array $labels = []): void
    {
        $this->graceful($name, function () use ($name, $labels, $value) {
            $gauge = $this->guage($name, $labels);
            $gauge->set($value, $this->labelValues($gauge, $labels));
        });
    }

    public function observe(string $name, float $value = 0, array $labels = [], ?array $buckets = null): void
    {
        $this->graceful($name, function () use ($name, $labels, $value, $buckets) {
            $histogram = $this->histogram($name, $labels, $buckets);
            $histogram->observe($value, $this->labelValues($histogram, $labels));
        });
    }

    public function summarize(string $name, float $value = 0, array $labels = [], int $maxAgeSeconds = 600, ?array $quantiles = null): void
    {
        $this->graceful($name, function () use ($name, $labels, $value, $maxAgeSeconds, $quantiles) {
            $summary = $this->summary($name, $labels, $maxAgeSeconds, $quantiles);
            $summary->observe($value, $this->labelValues($summary, $labels));
        });
    }

    protected function graceful(string $name, callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            $this->errorHandler->handle($e, $name);
        }
    }

    protected function labelNames(array $labels = []): array
    {
        $defaults = array_keys($this->labels);
        $customLabels = array_keys($labels);

        return array_unique(array_merge($defaults, $customLabels));
    }

    protected function labelValues(Collector $collector, array $labels = []): array
    {
        $all = array_merge($this->labels, $labels);

        // map the label values from $all in the order of the label names of the collector
        return array_map(function ($name) use ($all) {
            return $all[$name] ?? null;
        }, $collector->getLabelNames());
    }

}