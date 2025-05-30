# laravel-prometheus

A Laravel package for integrating [Prometheus](https://prometheus.io/) metrics into your Laravel application. This package provides a simple API for defining, registering, and exposing custom metrics, as well as a `/metrics` endpoint for Prometheus scraping.

## Features
- Register and expose counters, gauges, histograms, and summaries
- Simple API via Metrics and Prometheus facades
- Configurable default labels and metric definitions
- Built-in `/metrics` endpoint
- Optional basic authentication for metrics endpoint

## Installation

1. Install via Composer:
   ```bash
   composer require cloudisle/laravel-prometheus
   ```

2. (Optional) Publish the config files:
   ```bash
   php artisan vendor:publish --provider="CloudIsle\Prometheus\PrometheusServiceProvider" --tag=config
   ```

## Configuration

The package provides two config files:
- `config/prometheus.php`: Prometheus storage and endpoint settings
- `config/metrics.php`: Default labels and metric definitions

### prometheus.php
- `driver`: Storage driver for metrics (`memory`, `redis`, or `apcu`)
- `auth.enabled`: Enable basic auth for `/metrics` endpoint
- `auth.username` / `auth.password`: Credentials for basic auth

### metrics.php
- `namespace`: Default namespace for metrics
- `labels`: Default labels applied to all metrics (as an associative array)
- `counters`, `gauges`, `histograms`, `summaries`: Predefined metrics to register at boot. Each metric's `labels` should be an array of label names (not key-value pairs).

Example:
```php
return [
    'namespace' => 'myapp',
    'labels' => [
        'app' => 'myapp',
        'env' => env('APP_ENV'),
    ],
    'counters' => [
        [
            'name' => 'requests_total',
            'help' => 'Total HTTP requests',
            'labels' => ['route'], // array of label names
        ],
    ],
    // ...
];
```

## Usage

### Facades

#### Metrics
The `Metrics` facade provides a simple API for interacting with your metrics. When recording or incrementing metrics, you must provide label values in the same order as defined in the metric's `labels` array in the config. Default labels from the config are always prepended in order.

```php
use Metrics;

// Increment a counter (label values: [default labels..., route])
Metrics::increment('requests_total', ['path' => '/', 'route' => 'home']);

// Set a gauge
Metrics::record('memory_usage', 123.45, ['proc' => 'worker-1']);

// Observe a histogram
Metrics::observe('response_time', 0.234, ['resource' => 'api']);

// Summarize a value
Metrics::summarize('payload_size', 512, ['action' => 'upload']);
```

- If you provide a label value for a label defined in the config's `labels`, it will override the default value for that label.
- The order of label values must match the order of label names as defined in the metric's `labels` array, after the default labels.

#### Prometheus
The `Prometheus` facade gives you direct access to the underlying Prometheus client:

```php
use Prometheus;

// Get a counter instance
$counter = Prometheus::counter('my_ns', 'my_counter', 'Help text', ['foo', 'bar']);
$counter->incBy(1, ['val1', 'val2']);
```

### Metrics Endpoint

The `/metrics` route is automatically registered and returns all metrics in Prometheus text format. You can secure it with basic auth by enabling it in `prometheus.php` config.

## Error Handling

You can customize how metric registration errors are handled by binding your own implementation of `CloudIsle\Prometheus\MetricErrorHandler` in the service container.

## Testing

This package is fully testable with [Orchestra Testbench](https://github.com/orchestral/testbench). See the `tests/` directory for examples.

## License

MIT

