<?php

namespace DAMA\DoctrineTestBundle\Doctrine\DBAL;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\DriverException;
use Doctrine\DBAL\Driver\ExceptionConverterDriver;

class StaticDriver implements Driver, ExceptionConverterDriver
{
    /**
     * @var Connection[]
     */
    private static $connections = [];

    /**
     * @var bool
     */
    private static $keepStaticConnections = false;

    /**
     * @var string
     */
    private static $underlyingDriverClass;

    /**
     * @var Driver
     */
    private $underlyingDriver;

    public function __construct()
    {
        if (self::$underlyingDriverClass === null) {
            throw new \Exception('Cannot instantiate without setting underlying Driver class');
        }

        $this->underlyingDriver = new self::$underlyingDriverClass();
    }

    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        if (self::$keepStaticConnections) {
            $key = sha1(serialize($params).$username.$password);

            if (!isset(self::$connections[$key])) {
                self::$connections[$key] = $this->underlyingDriver->connect($params, $username, $password, $driverOptions);
                self::$connections[$key]->beginTransaction();
            }

            return new StaticConnection(self::$connections[$key]);
        }

        return $this->underlyingDriver->connect($params, $username, $password, $driverOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return $this->underlyingDriver->getDatabasePlatform();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
    {
        return $this->underlyingDriver->getSchemaManager($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->underlyingDriver->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase(\Doctrine\DBAL\Connection $conn)
    {
        return $this->underlyingDriver->getDatabase($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function convertException($message, DriverException $exception)
    {
        if ($this->underlyingDriver instanceof ExceptionConverterDriver) {
            return $this->underlyingDriver->convertException($message, $exception);
        }

        return $exception;
    }

    public static function beginTransaction()
    {
        foreach (self::$connections as $con) {
            try {
                $con->beginTransaction();
            } catch (\PDOException $e) {
                //transaction could be started already
            }
        }
    }

    public static function rollBack()
    {
        foreach (self::$connections as $con) {
            $con->rollBack();
        }
    }

    public static function commit()
    {
        foreach (self::$connections as $con) {
            $con->commit();
        }
    }

    /**
     * @param $keepStaticConnections bool
     */
    public static function setKeepStaticConnections($keepStaticConnections)
    {
        self::$keepStaticConnections = $keepStaticConnections;
    }

    /**
     * @param string $underlyingDriverClass
     */
    public static function setUnderlyingDriverClass($underlyingDriverClass)
    {
        self::$underlyingDriverClass = $underlyingDriverClass;
    }
}
