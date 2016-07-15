# Prooph EventStore Adapter Benchmarks

PHP 5.5+ EventStore Implementation.

[![Build Status](https://travis-ci.org/prooph/event-store-adapter-benchmarks.svg?branch=master)](https://travis-ci.org/prooph/event-store-adapter-benchmarks)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/prooph/improoph)

## Overview

This benchmark compares PHP 5.5, PHP 5.6 and PHP 7.0 with [mongodb-adapter](https://github.com/prooph/event-store-mongodb-adapter)
and [doctrine-adapter](https://github.com/prooph/event-store-doctrine-adapter) (using postgresql 9.4 and mysql 5.6).  

## Installation

You can install prooph/event-store-adapter-benchmarks via cloning this repository and calling `composer install`.
If you are running PHP 7, run this command afterwards: `composer require alcaeus/mongo-php-adapter ^1.0`.

## Benchmark results using PHP 7

Times in seconds

| Driver | Batch size: 1 | Batch size: 5 | Batch size: 10 | Batch Size 100 |
| :---   |      :---:    |      :---:    |      :---:     |     :---:      |
| doctrine-adapter (mysql) | 0.0667 | 0.1391 | 0.2365 | 1.6130 |
| doctrine-adapter (postgres) | 0.0185 | 0.0246 | 0.0250 | 0.0567 |
| mongodb-adapter | 0.0025 | 0.0021 | 0.0025 | 0.0116 |

## Support

- Ask questions on [prooph-users](https://groups.google.com/forum/?hl=de#!forum/prooph) mailing list.
- File issues at [https://github.com/prooph/event-store-adapter-benchmarks/issues](https://github.com/prooph/event-store-adapter-benchmarks/issues).
- Say hello in the [prooph gitter](https://gitter.im/prooph/improoph) chat.

## Contribute

Please feel free to fork and extend existing or add new plugins and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and may adapt the documentation.

## License

Released under the [New BSD License](LICENSE).
