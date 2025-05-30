<?php

namespace CloudIsle\Prometheus;

use Throwable;

interface MetricErrorHandler
{

    public function handle(Throwable $e, string $metricName): void;

}