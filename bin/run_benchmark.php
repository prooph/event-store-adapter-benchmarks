<?php
/*
 * This file is part of the prooph/event-store package.
 * (c) 2014 - 2016 prooph software GmbH <contact@prooph.de>
 * (c) 2014 - 2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

$repeats = 100;

$batchSizes = [1, 5, 10, 100];

$configs = [
    [
        'adapter' => 'mysql',
        'options' => [
            'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'password' => '',
            'dbname' => 'event_store_adapter_benchmarks',
            'charset' => 'utf8',
            'driverOptions' => [
                1002 => "SET NAMES 'UTF8'"
            ],
        ],
        'batchSizes' => $batchSizes,
    ],
    [
        'adapter' => 'postgres',
        'options' => [
            'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
            'host' => '127.0.0.1',
            'port' => '5432',
            'user' => 'postgres',
            'password' => '',
            'dbname' => 'event_store_adapter_benchmarks',
            'charset' => 'utf8',
        ],
        'batchSizes' => $batchSizes,
    ],
    [
        'adapter' => 'mongodb',
        'options' => [
            'db_name' => 'event_store_adapter_benchmarks',
            'mongo_connection_alias' => 'mongo_client',
        ],
        'batchSizes' => $batchSizes
    ],
];

$benchmark = new \Prooph\EventStore\AdapterBenchmarks\Benchmark($repeats);

$result = [];

foreach ($configs as $config) {
    $result[] = $benchmark->run($config);
}

var_dump($result);
