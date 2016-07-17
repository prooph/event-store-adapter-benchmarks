<?php
/*
 * This file is part of the prooph/event-store package.
 * (c) 2014 - 2016 prooph software GmbH <contact@prooph.de>
 * (c) 2014 - 2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Prooph\EventStore\AdapterBenchmarks\Benchmark;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

$repeats = 100;

$batchSizes = [1, 5, 10, 100];

$configs = [
    'mysql' => [
        'adapter' => 'mysql',
        'options' => [
            'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
            'host' => getenv('MYSQL_HOST') ?: '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => getenv('MYSQL_ROOT_PASSWORD') ?: '',
            'dbname' => 'event_store_adapter_benchmarks',
            'charset' => 'utf8',
            'driverOptions' => [
                1002 => "SET NAMES 'UTF8'"
            ],
        ],
        'batchSizes' => $batchSizes,
    ],
    'postgres' => [
        'adapter' => 'postgres',
        'options' => [
            'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
            'host' => getenv('POSTGRES_HOST') ?: '127.0.0.1',
            'port' => '5432',
            'user' => 'postgres',
            'password' => getenv('POSTGRES_PASSWORD') ?: '',
            'dbname' => 'event_store_adapter_benchmarks',
            'charset' => 'utf8',
        ],
        'batchSizes' => $batchSizes,
    ],
    'mongodb' => [
        'adapter' => 'mongodb',
        'options' => [
            'server' => 'mongodb://' . (getenv('MONGODB_HOST') ?: '127.0.0.1') . ':27017',
            'db_name' => 'event_store_adapter_benchmarks',
            'mongo_connection_alias' => 'mongo_client',
        ],
        'batchSizes' => $batchSizes
    ],
];

// wait for database
sleep(5);

$benchmark = new Benchmark($repeats);

$result = [];
$recursion = 0;

function benchmark(Benchmark $benchmark, array $configs, &$result)
{
    global $recursion;
    $benchmarkCase = getenv('BENCHMARK_CASE') ?: null;

    foreach ($configs as $case => $config) {
        if ($benchmarkCase === $case || null === $benchmarkCase) {
            try {
                $result[] = $benchmark->run($config);
            } catch (\Doctrine\DBAL\Exception\ConnectionException $ex) {
                if ($recursion > 10) {
                    throw $ex;
                }
                $recursion++;
                trigger_error($ex->getMessage(), E_USER_NOTICE);
                trigger_error("Waiting for database ...", E_USER_NOTICE);

                sleep(10);
                benchmark($benchmark, $configs, $result);
            }
        }
    }
}

benchmark($benchmark, $configs, $result);

var_dump($result);
