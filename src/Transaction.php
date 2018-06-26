<?php

namespace Amp\Sql;

use Amp\Promise;

interface Transaction extends Operation {
    const UNCOMMITTED  = 0;
    const COMMITTED    = 1;
    const REPEATABLE   = 2;
    const SERIALIZABLE = 4;

    /**
     * {@inheritdoc}
     *
     * Closes and commits all changes in the transaction.
     */
    public function close();

    /**
     * @return int
     */
    public function getIsolationLevel(): int;

    /**
     * {@inheritdoc}
     */
    public function isAlive(): bool;

    /**
     * @return bool True if the transaction is active, false if it has been committed or rolled back.
     */
    public function isActive(): bool;

    /**
     * {@inheritdoc}
     *
     * @throws \Amp\Sql\TransactionError If the transaction has been committed or rolled back.
     */
    public function query(string $sql): Promise;

    /**
     * {@inheritdoc}
     *
     * @throws \Amp\Sql\TransactionError If the transaction has been committed or rolled back.
     */
    public function prepare(string $sql): Promise;

    /**
     * {@inheritdoc}
     *
     * @throws \Amp\Sql\TransactionError If the transaction has been committed or rolled back.
     */
    public function execute(string $sql, array $params = []): Promise;

    /**
     * Commits the transaction and makes it inactive.
     *
     * @return \Amp\Promise<\Amp\Sql\CommandResult>
     *
     * @throws \Amp\Sql\TransactionError If the transaction has been committed or rolled back.
     */
    public function commit(): Promise;

    /**
     * Rolls back the transaction and makes it inactive.
     *
     * @return \Amp\Promise<\Amp\Sql\CommandResult>
     *
     * @throws \Amp\Sql\TransactionError If the transaction has been committed or rolled back.
     */
    public function rollback(): Promise;

    /**
     * Creates a savepoint with the given identifier.
     *
     * @param string $identifier Savepoint identifier.
     *
     * @return \Amp\Promise<\Amp\Sql\CommandResult>
     *
     * @throws \Amp\Sql\TransactionError If the transaction has been committed or rolled back.
     */
    public function savepoint(string $identifier): Promise;

    /**
     * Rolls back to the savepoint with the given identifier.
     *
     * @param string $identifier Savepoint identifier.
     *
     * @return \Amp\Promise<\Amp\Sql\CommandResult>
     *
     * @throws \Amp\Sql\TransactionError If the transaction has been committed or rolled back.
     */
    public function rollbackTo(string $identifier): Promise;

    /**
     * Releases the savepoint with the given identifier.
     *
     * @param string $identifier Savepoint identifier.
     *
     * @return \Amp\Promise<\Amp\Sql\CommandResult>
     *
     * @throws \Amp\Sql\TransactionError If the transaction has been committed or rolled back.
     */
    public function release(string $identifier): Promise;
}
