<?php

namespace Amp\Sql;

abstract class SqlConfig
{
    public const KEY_MAP = [
        'hostname' => 'host',
        'username' => 'user',
        'pass' => 'password',
        'database' => 'db',
        'dbname' => 'db',
    ];

    private string $host;

    private int $port;

    private ?string $user;

    private ?string $password;

    private ?string $database;

    /**
     * Parses a connection string into an array of keys and values given.
     *
     * @param string $connectionString Connection string, e.g., "hostname=localhost username=sql password=default"
     * @param string[] $keymap Map of alternative key names to canonical key names.
     *
     * @return string[]
     */
    protected static function parseConnectionString(string $connectionString, array $keymap = self::KEY_MAP): array
    {
        $values = [];

        $params = \explode(";", $connectionString);

        if (\count($params) === 1) { // Attempt to explode on a space if no ';' are found.
            $params = \explode(" ", $connectionString);
        }

        foreach ($params as $param) {
            /** @psalm-suppress PossiblyInvalidArgument */
            [$key, $value] = \array_map("trim", \explode("=", $param, 2) + [1 => ""]);

            if (isset($keymap[$key])) {
                $key = $keymap[$key];
            }

            $values[$key] = $value;
        }

        if (\preg_match('/^(.+):(\d{1,5})$/', $values["host"] ?? "", $matches)) {
            $values["host"] = $matches[1];
            $values["port"] = $matches[2];
        }

        return $values;
    }

    public function __construct(
        string $host,
        int $port,
        ?string $user = null,
        ?string $password = null,
        ?string $database = null
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
    }

    final public function getHost(): string
    {
        return $this->host;
    }

    final public function withHost(string $host): self
    {
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    final public function getPort(): int
    {
        return $this->port;
    }

    final public function withPort(int $port): self
    {
        $new = clone $this;
        $new->port = $port;
        return $new;
    }

    final public function getUser(): ?string
    {
        return $this->user;
    }

    final public function withUser(string $user = null): self
    {
        $new = clone $this;
        $new->user = $user;
        return $new;
    }

    final public function getPassword(): ?string
    {
        return $this->password;
    }

    final public function withPassword(string $password = null): self
    {
        $new = clone $this;
        $new->password = $password;
        return $new;
    }

    final public function getDatabase(): ?string
    {
        return $this->database;
    }

    final public function withDatabase(string $database = null): self
    {
        $new = clone $this;
        $new->database = $database;
        return $new;
    }
}
