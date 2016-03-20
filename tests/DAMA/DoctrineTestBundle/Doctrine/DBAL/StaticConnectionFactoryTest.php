<?php

namespace Tests\DAMA\DoctrineTestBundle\Doctrine\DBAL;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticConnectionFactory;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;

class StaticConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateConnection()
    {
        $factory = new StaticConnectionFactory([]);

        $connection = $factory->createConnection([
            'driverClass' => MockDriver::class,
        ]);

        $this->assertInstanceOf(StaticDriver::class, $connection->getDriver());
        $this->assertSame(1, $connection->getTransactionNestingLevel());
    }
}
