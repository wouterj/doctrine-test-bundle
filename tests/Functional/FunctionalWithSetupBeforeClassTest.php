<?php

namespace Tests\Functional;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class FunctionalWithSetupBeforeClassTest extends TestCase
{
    /**
     * @var KernelInterface
     */
    private static $kernel;

    /**
     * @var Connection
     */
    private $connection;

    public static function setUpBeforeClass(): void
    {
        self::$kernel = new AppKernel('test', true);
        self::$kernel->boot();
        self::$kernel->getContainer()->get('doctrine.dbal.default_connection')->insert('test', [
            'test' => 'setup_before_class',
        ]);
        StaticDriver::commit();
    }

    protected function setUp(): void
    {
        self::$kernel->shutdown();
        self::$kernel->boot();
        $this->connection = self::$kernel->getContainer()->get('doctrine.dbal.default_connection');
    }

    protected function tearDown(): void
    {
        self::$kernel->shutdown();
    }

    private function assertRowCount($count): void
    {
        $this->assertEquals($count, $this->connection->fetchColumn('SELECT COUNT(*) FROM test'));
    }

    private function insertRow(): void
    {
        $this->connection->insert('test', [
            'test' => 'foo',
        ]);
    }

    public function testChangeDbState(): void
    {
        $this->assertRowCount(1);
        $this->insertRow();
        $this->assertRowCount(2);
    }

    public function testPreviousChangesAreRolledBack(): void
    {
        $this->assertRowCount(1);
    }
}
