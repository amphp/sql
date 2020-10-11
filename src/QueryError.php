<?php

namespace Amp\Sql;

class QueryError extends \Error
{
    /** @var string */
    protected string $query = "";

    public function __construct(string $message, string $query = "", \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->query = $query;
    }

    final public function getQuery(): string
    {
        return $this->query;
    }

    public function __toString(): string
    {
        if ($this->query === "") {
            return parent::__toString();
        }

        $msg = $this->message;
        $this->message .= "\nCurrent query was {$this->query}";
        $str = parent::__toString();
        $this->message = $msg;
        return $str;
    }
}
