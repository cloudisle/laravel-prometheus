<?php

namespace CloudIsle\Prometheus;

use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricsRegistrationException;

class MetricRegistrar
{

    /**
     * @throws MetricsRegistrationException
     */
    public static function register(): void
    {
        $metrics = config('metrics');
        $namespace = config('metrics.namespace');

        $registry = app(CollectorRegistry::class);

        foreach ($metrics['counters'] as $counter) {
            $registry->registerCounter($namespace, $counter['name'], $counter['help'], static::labels($counter));
        }

        foreach ($metrics['gauges'] as $gauge) {
            $registry->registerGauge($namespace, $gauge['name'], $gauge['help'], static::labels($gauge));
        }

        foreach ($metrics['histograms'] as $histogram) {
            $registry->registerHistogram($namespace, $histogram['name'], $histogram['help'], static::labels($histogram), $histogram['buckets'] ?? null);
        }

        foreach ($metrics['summaries'] as $summary) {
            $registry->registerSummary($namespace, $summary['name'], $summary['help'], static::labels($summary), $summary['maxAgeSeconds'] ?? 600, $summary['quantiles'] ?? null);
        }
    }

    protected static function labels(array $collector): array
    {
        $defaults = config('metrics.labels', []);

        return array_merge(array_keys($defaults), array_values($collector['labels'] ?? []));
    }

}