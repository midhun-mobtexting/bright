<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Query;

use Diviky\Bright\Database\Bright;
use Diviky\Bright\Database\Concerns\Async;
use Diviky\Bright\Database\Concerns\Build;
use Diviky\Bright\Database\Concerns\BuildsQueries;
use Diviky\Bright\Database\Concerns\Cachable;
use Diviky\Bright\Database\Concerns\Eventable;
use Diviky\Bright\Database\Concerns\Filter;
use Diviky\Bright\Database\Concerns\Ordering;
use Diviky\Bright\Database\Concerns\Outfile;
use Diviky\Bright\Database\Concerns\Paging;
use Diviky\Bright\Database\Concerns\Raw;
use Diviky\Bright\Database\Concerns\Remove;
use Diviky\Bright\Database\Concerns\SoftDeletes;
use Diviky\Bright\Database\Concerns\Timestamps;
use Illuminate\Database\Query\Builder as LaravelBuilder;

class Builder extends LaravelBuilder
{
    use Async;
    use Build;
    use Cachable;
    use Eventable;
    use Filter;
    use Ordering;
    use Outfile;
    use Paging;
    use Raw;
    use Remove;
    use SoftDeletes;
    use Timestamps;
    use BuildsQueries;

    /**
     * Set the alias for table.
     *
     * @param string $as
     */
    public function alias($as): self
    {
        $this->from = "{$this->from} as {$as}";

        return $this;
    }

    /**
     * Set the alias for table.
     *
     * @param string $as
     */
    public function as($as): self
    {
        $this->from = "{$this->from} as {$as}";

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(array $values)
    {
        $values = $this->insertEvent($values);

        return parent::insert($values);
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param null|string $sequence
     *
     * @return int|string
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $values = $this->insertEvent($values);

        $id = parent::insertGetId($values[0], $sequence);

        if (empty($id)) {
            $id = $this->getLastId();
        }

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id = null)
    {
        $this->atomicEvent('delete');

        return parent::delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function update(array $values)
    {
        $values = $this->updateEvent($values);

        return parent::update($values);
    }

    /**
     * Excecute the RAW sql statement.
     *
     * @return array|bool|int
     */
    public function statement(string $sql, array $bindings = [])
    {
        $prefix = $this->connection->getTablePrefix();
        $sql = \str_replace('#__', $prefix, $sql);

        $type = \trim(\strtolower(\explode(' ', $sql)[0]));

        switch ($type) {
            case 'delete':
                return $this->connection->delete($sql, $bindings);

                break;
            case 'update':
                return $this->connection->update($sql, $bindings);

                break;
            case 'insert':
                return $this->connection->insert($sql, $bindings);

                break;
            case 'select':
                if (\preg_match('/outfile\s/i', $sql)) {
                    return $this->connection->statement($sql, $bindings);
                }

                return $this->connection->select($sql, $bindings);

                break;
            case 'load':
                return $this->connection->unprepared($sql);

                break;
        }

        return $this->connection->statement($sql, $bindings);
    }

    /**
     * Old cakephp style conditions.
     *
     * @param array $where
     * @param array $bindings
     */
    public function whereWith($where = [], $bindings = []): self
    {
        $sql = (new Bright())->conditions($where);

        return $this->whereRaw($sql, $bindings);
    }

    public function toQuery(): ?string
    {
        $this->atomicEvent('select');

        $sql = $this->toSql();

        foreach ($this->getBindings() as $binding) {
            $value = \is_numeric($binding) ? $binding : "'" . $binding . "'";
            $sql = \preg_replace('/\?/', $value, $sql, 1);
        }

        return $sql;
    }
}
