<?php
namespace Concept\DBC\PDO\Driver;

use Concept\Config\ConfigInterface;
use Concept\DBC\Driver\AbstractDriver;
use Concept\DBC\Driver\DriverInterface;
use Concept\DBC\Exception\RuntimeException;
use Concept\DBC\PDO\PDOConfigInterface;
use Concept\DBC\Result\ResultInterface;
use PDO;
use PDOStatement;

class PDODriver extends AbstractDriver implements DriverInterface
{

    private ?PDO $pdo = null;

    /**
     * Create a new result object
     * 
     * @param PDOStatement $statement
     * 
     * @return ResultInterface
     */
    protected function createResult(PDOStatement $statement): ResultInterface
    {
        return $this->getResultPrototype()
            ->withStatement($statement);
    }

    /**
     * Get the PDO object
     * 
     * @return PDO|null
     */
    protected function getPDO(): ?PDO
    {
        return $this->pdo;
    }

    /**
     * Initialize the PDO object
     * 
     * @param ConfigInterface $config
     * 
     * @return void
     */
    protected function initPDO(ConfigInterface $config): void
    {
        try{
            $this->pdo = new PDO(
                $this->createDSN($config),
                $config->get(PDOConfigInterface::USERNAME),
                $config->get(PDOConfigInterface::PASSWORD),
                array_merge(
                    [
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ],
                    $config->get(PDOConfigInterface::OPTIONS) ?? []
                )
                
            );
        } catch (\PDOException $e) {
            throw new RuntimeException('Could not connect to database', 0, $e);
        }
    }

    /**
     * Create a DSN string
     * 
     * @param ConfigInterface $config
     * 
     * @return string
     */
    protected function createDSN(ConfigInterface $config): string
    {
        $dsn = $config->get('dsn') ??
            sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config->get(PDOConfigInterface::DRIVER),
                $config->get(PDOConfigInterface::HOST),
                $config->get(PDOConfigInterface::PORT),
                $config->get(PDOConfigInterface::DATABASE),
                $config->get(PDOConfigInterface::CHARSET)
            );
        
        if (is_array($dsn)) {
            $dsn = join(';', array_map(
                fn($key, $value) => "$key=$value",
                array_keys($dsn),
                array_values($dsn)
            ));
        }

        return $dsn;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(ConfigInterface $config): static
    {

        if ($this->getPDO() === null) {
            $this->initPDO($config);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function disconnect(): static
    {
        $this->pdo = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected(): bool
    {
        return $this->getPDO() !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $sql, array $params = []): ResultInterface
    {
        try {   
            //$this->beginTransaction();

            $stmt = $this->getPDO()
                ->prepare((string)$sql);

            $success = $stmt->execute($params);

            if (!$success) {
                throw new RuntimeException(
                    sprintf(
                        'Query failed: %s',
                        join(
                            ', ', 
                            array_map(
                                fn($infoKey, $infoValue) => sprintf('%s: %s', $infoKey, $infoValue),
                                array_keys($stmt->errorInfo()),
                                array_values($stmt->errorInfo())
                            )
                        )
                    )
                );
            }

            if ($this->inTransaction()) {
                $this->commit();
            }
        } catch (\PDOException $e) {
            if ($this->inTransaction()) {
            	$this->rollback();
            }

            throw new RuntimeException('Query failed.'.$e->getMessage(), 0, $e);
        }
        
        return $this->createResult($stmt);;
    }

    /**
     * {@inheritDoc}
     */
    public function exec(string $sql, array $params = []): int|bool
    {
        return $this->getPDO()
            ->exec($sql);
    }

    // public function prepare(string $sql, array $options = []): mixed
    // {
    //     return $this->getPDO()->prepare($sql, $options);
    // }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction(): bool
    {
        return $this->getPDO()
            ->beginTransaction();
    }

    /**
     * {@inheritDoc}
     */
    public function commit(): bool
    {
        return $this->getPDO()
            ->commit();
    }

    /**
     * {@inheritDoc}
     */
    public function rollback(): bool
    {
        return $this->getPDO()
            ->rollBack();
    }

    /**
     * {@inheritDoc}
     */
    public function inTransaction(): bool
    {
        return $this->getPDO()
            ->inTransaction();
    }

    public function quote(string $string): string
    {
        return $this->getPDO()->quote($string);
    }

    /**
     * {@inheritDoc}
     */
    public function escape(string $value): string
    {
        return $this->getPDO()->quote($value);
    }

}