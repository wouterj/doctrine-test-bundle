<?php

namespace DAMA\DoctrineTestBundle\Doctrine\DBAL;

use Doctrine\DBAL\Driver\Connection;

/**
 * Wraps a real connection and just skips the first call to beginTransaction as a transaction is already started on the underlying connection.
 */
class StaticConnection implements Connection
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var bool
     */
    private $transactionStarted = false;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Prepares a statement for execution and returns a Statement object.
     *
     * @param string $prepareString
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function prepare($prepareString)
    {
        return $this->connection->prepare($prepareString);
    }

    /**
     * Executes an SQL statement, returning a result set as a Statement object.
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function query()
    {
        return call_user_func_array([$this->connection, 'query'], func_get_args());
    }

    /**
     * Quotes a string for use in a query.
     *
     * @param string $input
     * @param int    $type
     *
     * @return string
     */
    public function quote($input, $type = \PDO::PARAM_STR)
    {
        return $this->connection->quote($input, $type);
    }

    /**
     * Executes an SQL statement and return the number of affected rows.
     *
     * @param string $statement
     *
     * @return int
     */
    public function exec($statement)
    {
        return $this->connection->exec($statement);
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @param string|null $name
     *
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return $this->connection->lastInsertId($name);
    }

    /**
     * Initiates a transaction.
     *
     * @return bool TRUE on success or FALSE on failure.
     */
    public function beginTransaction()
    {
        if ($this->transactionStarted) {
            return $this->connection->beginTransaction();
        }

        return $this->transactionStarted = true;
    }

    /**
     * Commits a transaction.
     *
     * @return bool TRUE on success or FALSE on failure.
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Rolls back the current transaction, as initiated by beginTransaction().
     *
     * @return bool TRUE on success or FALSE on failure.
     */
    public function rollBack()
    {
        return $this->connection->rollBack();
    }

    /**
     * Returns the error code associated with the last operation on the database handle.
     *
     * @return string|null The error code, or null if no operation has been run on the database handle.
     */
    public function errorCode()
    {
        return $this->connection->errorCode();
    }

    /**
     * Returns extended error information associated with the last operation on the database handle.
     *
     * @return array
     */
    public function errorInfo()
    {
        return $this->connection->errorInfo();
    }
}
