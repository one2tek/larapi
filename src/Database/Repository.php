<?php

namespace Gentritabazi01\LarapiComponents\Database;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Gentritabazi01\LarapiComponents\Database\EloquentBuilderTrait;

abstract class Repository
{
    use EloquentBuilderTrait;

    protected $model;

    protected $sortProperty = null;

    // 0 = ASC, 1 = DESC
    protected $sortDirection = 0;

    abstract protected function getModel();

    final public function __construct()
    {
        $this->model = $this->getModel();
    }

    /**
     * Get all resources.
     * 
     * @param  array  $options
     * 
     * @return Collection
     */
    public function get(array $options = [])
    {
        $query = $this->createBaseBuilder($options);

        return $query->get();
    }

    /**
     * Get a resource by its primary key.
     * 
     * @param  mixed  $id
     * @param  array  $options
     * 
     * @return Collection
     */
    public function getById($id, array $options = [])
    {
        $query = $this->createBaseBuilder($options);

        return $query->find($id);
    }

    /**
     * Get all resources ordered by recentness.
     * 
     * @param  array  $options
     * 
     * @return Collection
     */
    public function getRecent(array $options = [])
    {
        $query = $this->createBaseBuilder($options);

        $query->orderBy($this->getCreatedAtColumn(), 'DESC');

        return $query->get();
    }

    /**
     * Get all resources by a where clause ordered by recentness.
     * 
     * @param  string  $column
     * @param  mixed  $value
     * @param  array   $options
     * 
     * @return Collection
     */
    public function getRecentWhere($column, $value, array $options = [])
    {
        $query = $this->createBaseBuilder($options);

        $query->where($column, $value);

        $query->orderBy($this->getCreatedAtColumn(), 'DESC');

        return $query->get();
    }

    /**
     * Get latest resource.
     * 
     * @param  array  $options
     * 
     * @return Collection
     */
    public function getLatest(array $options = [])
    {
        $query = $this->createBaseBuilder($options);

        $query->orderBy($this->getCreatedAtColumn(), 'DESC');

        return $query->first();
    }

    /**
     * Get latest resource by a where clause.
     * 
     * @param  string  $column
     * @param  mixed  $value
     * @param  array   $options
     * 
     * @return Collection
     */
    public function getLatestWhere($column, $value, array $options = [])
    {
        $query = $this->createBaseBuilder($options);

        $query->where($column, $value);

        $query->orderBy($this->getCreatedAtColumn(), 'DESC');

        return $query->first();
    }

    /**
     * Get resources by a where clause.
     * 
     * @param  string  $column
     * @param  mixed  $value
     * @param  array  $options
     * 
     * @return Collection
     */
    public function getWhere($column, $value, array $options = [])
    {
        $query = $this->createBaseBuilder($options);

        $query->where($column, $value);

        return $query->get();
    }

    /**
     * Get resources by multiple where clauses.
     * 
     * @param  array   $clauses
     * @param  array  $options
     * 
     * @return Collection
     */
    public function getWhereArray(array $clauses, array $options = [])
    {
        $query = $this->createBaseBuilder($options);

        $this->applyWhereArray($query, $clauses);

        return $query->get();
    }

    /**
     * Get resources where a column value exists in array.
     * 
     * @param  string  $column
     * @param  array   $values
     * @param  array  $options
     * 
     * @return Collection
     */
    public function getWhereIn($column, array $values, array $options = [])
    {
        $query = $this->createBaseBuilder($options);

        $query->whereIn($column, $values);

        return $query->get();
    }

    /**
     * Delete a resource by its primary key.
     * 
     * @param  mixed  $id
     * 
     * @return void
     */
    public function delete($id)
    {
        $query = $this->createQueryBuilder();

        $query->where($this->getPrimaryKey($query), $id);
        $query->delete();
    }

    /**
     * Delete resources by a where clause.
     * 
     * @param  string  $column
     * @param  mixed  $value
     * 
     * @return void
     */
    public function deleteWhere($column, $value)
    {
        $query = $this->createQueryBuilder();

        $query->where($column, $value);
        $query->delete();
    }

    /**
     * Delete resources by multiple where clauses.
     * 
     * @param  array  $clauses
     * 
     * @return void
     */
    public function deleteWhereArray(array $clauses)
    {
        $query = $this->createQueryBuilder();

        $this->applyWhereArray($query, $clauses);
        $query->delete();
    }

    /**
     * Creates a new query builder with options set.
     * 
     * @param  array  $options
     * 
     * @return Builder
     */
    protected function createBaseBuilder(array $options = [])
    {
        $query = $this->createQueryBuilder();

        $this->applyResourceOptions($query, $options);

        if (empty($options['sort'])) {
            $this->defaultSort($query, $options);
        }

        return $query;
    }

    /**
     * Creates a new query builder.
     * 
     * @return Builder
     */
    protected function createQueryBuilder()
    {
        return $this->model->newQuery();
    }

    /**
     * Get primary key name of the underlying model.
     * 
     * @param  Builder  $query
     * 
     * @return string
     */
    protected function getPrimaryKey($query)
    {
        return $query->getModel()->getKeyName();
    }

    /**
     * Order query by the specified sorting property.
     * 
     * @param  Builder  $query
     * @param  array   $options
     * 
     * @return void
     */
    protected function defaultSort($query, array $options = [])
    {
        if (isset($this->sortProperty)) {
            $direction = $this->sortDirection === 1 ? 'DESC' : 'ASC';
            $query->orderBy($this->sortProperty, $direction);
        }
    }

    /**
     * Get the name of the "created at" column.
     * 
     * @return string
     */
    protected function getCreatedAtColumn()
    {
        $model = $this->model;
        return ($model::CREATED_AT) ? $model::CREATED_AT : 'created_at';
    }

    protected function applyWhereArray($query, array $clauses)
    {
        foreach ($clauses as $key => $value) {
            preg_match('/NOT\:(.+)/', $key, $matches);

            $not = false;
            if (isset($matches[1])) {
                $not = true;
                $key = $matches[1];
            }

            if (is_array($value)) {
                if (!$not) {
                    $query->whereIn($key, $value);
                } else {
                    $query->whereNotIn($key, $value);
                }
            } elseif (is_null($value)) {
                if (!$not) {
                    $query->whereNull($key);
                } else {
                    $query->whereNotNull($key);
                }
            } else {
                if (!$not) {
                    $query->where($key, $value);
                } else {
                    $query->where($key, '!=', $value);
                }
            }
        }
    }
}
