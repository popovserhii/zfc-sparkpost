<?php

namespace Popov\ZfcSparkPost\Transport\Factory;

use Interop\Container\ContainerInterface;

class SparkPostFactory
{
    public function __invoke(ContainerInterface $container)
    {
        die(__METHOD__);
    }
}