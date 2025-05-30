<?php

namespace CloudIsle\Prometheus;

use Illuminate\Support\Facades\Log;
use Throwable;

class LogMetricErrorHandler implements MetricErrorHandler
{

    public function handle(Throwable $e, string $metricName): void
    {
        Log::error("Error while handling metric '$metricName': " . $e->getMessage(), [
            'exception' => $e,
            'metric' => $metricName,
        ]);
    }

}