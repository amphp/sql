<?php

namespace Amp\Sql;

interface Executor extends TransientResource
{
    /**
     * @param string $sql SQL query to execute.
     *
     * @return Result
     *
     * @throws FailureException If the operation fails due to unexpected condition.
     * @throws ConnectionException If the connection to the database is lost.
     * @throws QueryError If the operation fails due to an error in the query (such as a syntax error).
     */
    public function query(string $sql): Result;

    /**
     * @param string $sql SQL query to prepare.
     *
     * @return Statement
     *
     * @throws FailureException If the operation fails due to unexpected condition.
     * @throws ConnectionException If the connection to the database is lost.
     * @throws QueryError If the operation fails due to an error in the query (such as a syntax error).
     */
    public function prepare(string $sql): Statement;

    /**
     * @param string $sql SQL query to prepare and execute.
     * @param mixed[] $params Query parameters.
     *
     * @return Result
     *
     * @throws FailureException If the operation fails due to unexpected condition.
     * @throws ConnectionException If the connection to the database is lost.
     * @throws QueryError If the operation fails due to an error in the query (such as a syntax error).
     */
    public function execute(string $sql, array $params = []): Result;

    /**
     * Closes the executor. No further queries may be performed.
     */
    public function close(): void;
}
