<?php

namespace CloudIsle\Prometheus;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use PDO;
use Prometheus\CollectorRegistry;
use Prometheus\Exception\StorageException;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\PDO as PDOAdapter;
use Prometheus\Storage\Redis as RedisAdapter;

class CollectorRegistryFactory
{

    /**
     * @throws Exception
     */
    public static function create(string $driver): CollectorRegistry
    {
        return match ($driver) {
            'redis' => static::createRedisCollectorRegistry(),
            'database' => static::createDatabaseCollectorRegistry(),
            'memory' => static::createMemoryCollectorRegistry(),
            'pdo' => static::createPdoCollectorRegistry(),
        };
    }

    /**
     * @throws StorageException
     */
    protected static function createRedisCollectorRegistry(): CollectorRegistry
    {
        $conn = Redis::connection(config('prometheus.redis.connection'));
        $adapter = RedisAdapter::fromExistingConnection($conn->client());

        return new CollectorRegistry($adapter);
    }


    protected static function createDatabaseCollectorRegistry(): CollectorRegistry
    {
        $db = DB::connection(config('prometheus.database.connection'));
        $adapter = new PDOAdapter($db->getPdo(), config('prometheus.database.prefix'));

        return new CollectorRegistry($adapter);
    }

    protected static function createMemoryCollectorRegistry(): CollectorRegistry
    {
        return new CollectorRegistry(new InMemory());
    }

    protected static function createPdoCollectorRegistry(): CollectorRegistry
    {
        $db = new PDO(
            config('prometheus.pdo.dsn'),
            config('prometheus.pdo.username'),
            config('prometheus.pdo.password'),
            config('prometheus.pdo.options')
        );

        return new CollectorRegistry(new PDOAdapter($db, config('prometheus.pdo.prefix')));
    }

}