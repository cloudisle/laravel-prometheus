<?php

namespace CloudIsle\Prometheus\Http;

use CloudIsle\Prometheus\PrometheusService;
use Illuminate\Http\Response;
use Prometheus\RenderTextFormat;
use Throwable;

class MetricEndpoint
{

    private PrometheusService $service;
    private RenderTextFormat $renderer;

    public function __construct(PrometheusService $service)
    {
        $this->service = $service;
        $this->renderer = new RenderTextFormat();
    }

    /**
     * @throws Throwable
     */
    public function __invoke(): Response
    {
        $metrics = $this->service->metrics();

        $result = $this->renderer->render($metrics);

        return response($result, 200)
            ->header('Content-Type', RenderTextFormat::MIME_TYPE);
    }

}