<?php

namespace Tests\DAMA\DoctrineTestBundle\Doctrine\DBAL;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticConnectionFactory;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;

class StaticConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider createConnectionDataProvider
     *
     * @param bool $keepStaticConnections
     * @param int $expectedNestingLevel
     */
    public function testCreateConnection($keepStaticConnections, $expectedNestingLevel)
    {
        $factory = new StaticConnectionFactory([]);

        StaticDriver::setKeepStaticConnections($keepStaticConnections);

        $connection = $factory->createConnection([
            'driverClass' => MockDriver::class,
        ]);

        $this->assertInstanceOf(StaticDriver::class, $connection->getDriver());
        $this->assertSame($expectedNestingLevel, $connection->getTransactionNestingLevel());
    }

    public function createConnectionDataProvider()
    {
        return [
            [false, 0],
            [true, 1],
        ];
    }
}
