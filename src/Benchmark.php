<?php
/*
 * This file is part of the prooph/event-store package.
 * (c) 2014 - 2016 prooph software GmbH <contact@prooph.de>
 * (c) 2014 - 2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prooph\EventStore\AdapterBenchmarks;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Predis\Client as PredisClient;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\EventStore\Adapter\Doctrine\DoctrineEventStoreAdapter;
use Prooph\EventStore\Adapter\MongoDb\MongoDbEventStoreAdapter;
use Prooph\EventStore\Adapter\PayloadSerializer\JsonPayloadSerializer;
use Prooph\EventStore\Adapter\Predis\PredisEventStoreAdapter;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\Stream;
use Prooph\EventStore\Stream\StreamName;
use ProophTest\EventStore\Mock\UserCreated;
use ProophTest\EventStore\Mock\UsernameChanged;

/**
 * Class Benchmark
 * @package Prooph\EventStore\AdapterBenchmarks
 */
class Benchmark
{
    /**
     * @var int
     */
    private $repeats;

    /**
     * Benchmark constructor.
     * @param int $repeats
     */
    public function __construct($repeats)
    {
        if (! is_numeric($repeats)) {
            throw new \InvalidArgumentException('$repeats must be an integer');
        }

        $this->repeats = $repeats;
    }

    /**
     * @param array $data
     * @return array
     */
    public function run(array $data)
    {
        switch ($data['adapter']) {
            case 'mysql':
                $connection = DriverManager::getConnection($data['options']);
                $connection->executeQuery('DROP TABLE IF EXISTS user_stream');
                $adapter = new DoctrineEventStoreAdapter(
                    $connection,
                    new FQCNMessageFactory(),
                    new NoOpMessageConverter(),
                    new JsonPayloadSerializer()
                );
                break;
            case 'postgres':
                $connection = DriverManager::getConnection($data['options']);
                $connection->executeQuery('DROP TABLE IF EXISTS user_stream');
                $adapter = new DoctrineEventStoreAdapter(
                    $connection,
                    new FQCNMessageFactory(),
                    new NoOpMessageConverter(),
                    new JsonPayloadSerializer()
                );
                break;
            case 'mongodb':
                $connection = new \MongoClient($data['options']['server']);
                $connection->selectDB('event_store_adapter_benchmarks')->selectCollection('user_stream')->drop();
                $adapter = new MongoDbEventStoreAdapter(
                    new FQCNMessageFactory(),
                    new NoOpMessageConverter(),
                    $connection,
                    $data['options']['db_name']
                );
                break;
            case 'predis':
                $connection = new PredisClient($data['options']['server']);
                $adapter = new PredisEventStoreAdapter(
                    $connection,
                    new FQCNMessageFactory(),
                    new NoOpMessageConverter(),
                    new JsonPayloadSerializer()
                );
                break;
            default:
                throw new \InvalidArgumentException('invalid adapter given');
                break;
        }

        $eventStore = new EventStore($adapter, new ProophActionEventEmitter());

        $result = [];

        foreach ($data['batchSizes'] as $batchSize) {
            for ($i = 0; $i < $this->repeats; $i++) {
                $events = [];

                $start = microtime(true);

                for ($b = 1; $b <= $batchSize *2; $b++) {
                    $v1 = $b;
                    $b++;
                    $v2 = $b;
                    $events[] = UserCreated::with(
                        [
                            'name' => 'Max Mustermann ' . $i . '-' . $b,
                            'email' => 'contact' . $i . '-' . $b . '@prooph.de'
                        ],
                        $v1
                    );

                    $events[] = UsernameChanged::with(
                        [
                            'name' => 'John Doe ' . $i . '-' . $b
                        ],
                        $v2
                    );
                }

                $eventStore->beginTransaction();
                $eventStore->create(new Stream(new StreamName('user_stream'), new \ArrayIterator($events)));
                $eventStore->commit();

                $end = microtime(true);

                switch (true) {
                    case $connection instanceof Connection:
                        $connection->executeQuery('DROP TABLE IF EXISTS user_stream');
                        break;
                    case $connection instanceof \MongoClient:
                        $connection->selectDB('event_store_adapter_benchmarks')->selectCollection('user_stream')->drop();
                        break;
                    case $connection instanceof PredisClient:
                        $connection->flushall();
                        break;
                }

                $diff = $end - $start;

                $result[$data['adapter'] . ' with batch size ' . $batchSize] = $diff;
            }
        }

        return $result;
    }
}
