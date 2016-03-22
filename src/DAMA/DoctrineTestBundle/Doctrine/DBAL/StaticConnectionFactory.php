<?php

namespace DAMA\DoctrineTestBundle\Doctrine\DBAL;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;

class StaticConnectionFactory extends ConnectionFactory
{
    /**
     * @param array         $params
     * @param Configuration $config
     * @param EventManager  $eventManager
     * @param array         $mappingTypes
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function createConnection(array $params, Configuration $config = null, EventManager $eventManager = null, array $mappingTypes = array())
    {
        if (isset($params['driverClass'])) {
            $underlyingDriverClass = $params['driverClass'];
        } else {
            //there seems to be no other way to access the originally configured Driver class :(
            $connectionOriginalDriver = parent::createConnection($params, $config, $eventManager, $mappingTypes);
            $underlyingDriverClass = get_class($connectionOriginalDriver->getDriver());
        }

        StaticDriver::setUnderlyingDriverClass($underlyingDriverClass);

        $params['driverClass'] = StaticDriver::class;
        $connection = parent::createConnection($params, $config, $eventManager, $mappingTypes);

        if (StaticDriver::isKeepStaticConnections()) {
            // The underlying connection already has a transaction started.
            // So we start it as well on this connection so the internal state ($_transactionNestingLevel) is in sync with the underlying connection.
            $connection->beginTransaction();
        }

        return $connection;
    }
}
