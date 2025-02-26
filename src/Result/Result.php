<?php
namespace Concept\DBC\PDO\Result;

use Concept\DBC\Result\ResultInterface;
use Concept\Prototype\PrototypableInterface;
use Concept\Prototype\PrototypableTrait;
use IteratorAggregate;
use PDO;
use PDOStatement;
use Traversable;

class Result implements ResultInterface
{
    use PrototypableTrait;

    private ?\PDOStatement $statement = null;
    protected int $fetchMode = PDO::FETCH_ASSOC;

    /**
     * Clone the Result object.
     */
    public function __clone()
    {
        
    }

    public function reset(): static
    {
        $this->statement = null;
        $this->fetchMode = PDO::FETCH_ASSOC;

        return $this;
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        while ($row = $this->fetch()) {
            yield $row;
        }
    }

    /**
     * Set the PDOStatement object.
     * 
     * @param PDOStatement $statement
     * 
     * @return static
     */
    public function withStatement(\PDOStatement $statement): static
    {
        $this->statement = $statement;

        return $this;
    }

    /**
     * Get the PDOStatement object.
     * 
     * @return PDOStatement
     */
    protected function getStatement(): PDOStatement
    {
        if ($this->statement === null) {
            throw new \RuntimeException('Statement not set');
        }

        return $this->statement;
    }

    /**
     * {@inheritDoc}
     */
    public function errorCode(): ?int
    {
        return $this->getStatement()->errorCode();
    }

    /**
     * {@inheritDoc}
     */
    public function errorInfo(): ?array
    {
        return $this->getStatement()->errorInfo();
    }

    /**
     * {@inheritDoc}
     */
    public function errorMessage(): ?string
    {
        return $this->errorInfo()[2] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(): mixed
    {
        return $this->getStatement()->fetch($this->getFetchMode());
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAll(): array
    {
        return $this->getStatement()->fetchAll($this->getFetchMode());
    }

    /**
     * {@inheritDoc}
     */
    public function rowCount(): int
    {
        return $this->getStatement()->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function lastInsertId(): mixed
    {
        return $this->getStatement()->lastInsertId();
    }

    /**
     * {@inheritDoc}
     */
    public function setFetchMode(int $fetchMode): static
    {
        $this->fetchMode = $fetchMode;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFetchMode(): int
    {
        return $this->fetchMode;
    }
}