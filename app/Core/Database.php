<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;
use Throwable;

/**
 * Thin PDO wrapper. Every query goes through prepared statements;
 * there is intentionally no API that interpolates values into SQL.
 */
final class Database
{
    private ?PDO $pdo = null;
    private int $transactionLevel = 0;

    public function __construct(private readonly array $config)
    {
    }

    public function pdo(): PDO
    {
        if ($this->pdo === null) {
            $c = $this->config;
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $c['host'],
                (int) $c['port'],
                $c['database']
            );
            if (!empty($c['socket'])) {
                $dsn = sprintf('mysql:unix_socket=%s;dbname=%s;charset=utf8mb4', $c['socket'], $c['database']);
            }
            $this->pdo = new PDO($dsn, $c['username'], $c['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]);
            $this->pdo->exec("SET time_zone = '+00:00', sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION'");
        }
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo()->prepare($sql);
        foreach ($params as $key => $value) {
            $name = is_int($key) ? $key + 1 : (str_starts_with($key, ':') ? $key : ':' . $key);
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
            $stmt->bindValue($name, $value, $type);
        }
        $stmt->execute();
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function value(string $sql, array $params = []): mixed
    {
        $value = $this->query($sql, $params)->fetchColumn();
        return $value === false ? null : $value;
    }

    public function column(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_COLUMN);
    }

    public function insert(string $table, array $data): int
    {
        $this->assertIdentifier($table);
        $columns = array_keys($data);
        array_walk($columns, fn ($c) => $this->assertIdentifier($c));
        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $table,
            implode(', ', array_map(fn ($c) => "`$c`", $columns)),
            implode(', ', array_map(fn ($c) => ":$c", $columns))
        );
        $this->query($sql, $data);
        return (int) $this->pdo()->lastInsertId();
    }

    public function update(string $table, array $data, array $where): int
    {
        $this->assertIdentifier($table);
        $sets = [];
        $params = [];
        foreach ($data as $column => $value) {
            $this->assertIdentifier($column);
            $sets[] = "`$column` = :set_$column";
            $params["set_$column"] = $value;
        }
        $conditions = [];
        foreach ($where as $column => $value) {
            $this->assertIdentifier($column);
            $conditions[] = "`$column` = :where_$column";
            $params["where_$column"] = $value;
        }
        $sql = sprintf('UPDATE `%s` SET %s WHERE %s', $table, implode(', ', $sets), implode(' AND ', $conditions));
        return $this->query($sql, $params)->rowCount();
    }

    public function delete(string $table, array $where): int
    {
        $this->assertIdentifier($table);
        $conditions = [];
        $params = [];
        foreach ($where as $column => $value) {
            $this->assertIdentifier($column);
            $conditions[] = "`$column` = :$column";
            $params[$column] = $value;
        }
        return $this->query(sprintf('DELETE FROM `%s` WHERE %s', $table, implode(' AND ', $conditions)), $params)->rowCount();
    }

    /**
     * Run a callback inside a transaction. Nested calls reuse the outer
     * transaction so services can compose safely.
     */
    public function transaction(callable $callback): mixed
    {
        $pdo = $this->pdo();
        if ($this->transactionLevel === 0) {
            $pdo->beginTransaction();
        }
        $this->transactionLevel++;
        try {
            $result = $callback($this);
            $this->transactionLevel--;
            if ($this->transactionLevel === 0) {
                $pdo->commit();
            }
            return $result;
        } catch (Throwable $e) {
            $this->transactionLevel--;
            if ($this->transactionLevel === 0 && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Test support: open an outer transaction and account for it in the
     * nesting counter, so service-layer transaction() calls nest cleanly and
     * the whole test can be rolled back afterwards.
     */
    public function beginTestTransaction(): void
    {
        if (!$this->pdo()->inTransaction()) {
            $this->pdo()->beginTransaction();
        }
        $this->transactionLevel = 1;
    }

    public function rollBackTestTransaction(): void
    {
        if ($this->pdo()->inTransaction()) {
            $this->pdo()->rollBack();
        }
        $this->transactionLevel = 0;
    }

    /**
     * Build a "?, ?, ?" placeholder list for IN() clauses.
     */
    public static function placeholders(array $values): string
    {
        return implode(', ', array_fill(0, max(1, count($values)), '?'));
    }

    private function assertIdentifier(string $name): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new \InvalidArgumentException('Invalid SQL identifier.');
        }
    }
}
