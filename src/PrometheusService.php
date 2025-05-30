<?php

namespace CloudIsle\Prometheus;

use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\Summary;
use Throwable;

class PrometheusService
{

    private CollectorRegistry $registry;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @throws MetricsRegistrationException
     */
    public function counter(string $namespace, string $name, string $help, array $labels = []): Counter
    {
        return $this->registry->getOrRegisterCounter($namespace, $name, $help, $labels);
    }

    /**
     * @throws MetricsRegistrationException
     */
    public function gauge(string $namespace, string $name, string $help, array $labels = []): Gauge
    {
        return $this->registry->getOrRegisterGauge($namespace, $name, $help, $labels);
    }

    /**
     * @throws MetricsRegistrationException
     */
    public function histogram(string $namespace, string $name, string $help, array $labels = [], ?array $buckets = null): Histogram
    {
        return $this->registry->getOrRegisterHistogram($namespace, $name, $help, $labels, $buckets);
    }

    /**
     * @throws MetricsRegistrationException
     */
    public function summary(string $namespace,
                            string $name,
                            string $help,
                            array $labels = [],
                            int $maxAgeSeconds = 600,
                            ?array $quantiles = null): Summary
    {
        return $this->registry->getOrRegisterSummary(
            $namespace,
            $name,
            $help,
            $labels,
            $maxAgeSeconds,
            $quantiles
        );
    }

    /**
     * @throws Throwable
     */
    public function metrics(): array
    {
        return $this->registry->getMetricFamilySamples();
    }

}