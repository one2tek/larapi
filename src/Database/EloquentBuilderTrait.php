<?php

namespace one2tek\larapi\Database;

use DB;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait EloquentBuilderTrait
{
    protected function applyResourceOptions(Builder $queryBuilder, array $options = [])
    {
        if (empty($options)) {
            return $queryBuilder;
        }

        extract($options);

        if (isset($selects)) {
            if (!is_array($selects)) {
                throw new InvalidArgumentException('Selects should be an array.');
            }

            if (count($selects)) {
                $queryBuilder->select(array_unique($selects));
            }
        }

        if (isset($includes)) {
            if (!is_array($includes)) {
                throw new InvalidArgumentException('Includes should be an array.');
            }

            $queryBuilder->with($includes);
        }
        
        if (isset($withCount)) {
            if (!is_array($withCount)) {
                throw new InvalidArgumentException('withCount should be an array.');
            }

            $queryBuilder->withCount($withCount);
        }
        
        if (isset($exludeGlobalScopes)) {
            if (!is_array($exludeGlobalScopes)) {
                throw new InvalidArgumentException('exludeGlobalScopes should be an array.');
            }

            $this->applyWithouGlobalScopes($queryBuilder, $exludeGlobalScopes);
        }

        if (isset($filter_groups)) {
            $this->applyFilterGroups($queryBuilder, $filter_groups);
        }

        if (isset($sort)) {
            if (!is_array($sort)) {
                throw new InvalidArgumentException('Sort should be an array.');
            }

            $this->applySorting($queryBuilder, $sort);
        }

        if (isset($limit)) {
            $queryBuilder->limit($limit);
        }

        if (isset($start)) {
            $queryBuilder->offset($start);
        } elseif (isset($page)) {
            if (!isset($limit)) {
                throw new InvalidArgumentException('A limit is required when using page.');
            }

            $queryBuilder->offset($page * $limit);
        }

        if (isset($distinct)) {
            $queryBuilder->distinct();
        }

        return $queryBuilder;
    }

    protected function applyFilterGroups(Builder $queryBuilder, array $filterGroups = [])
    {
        foreach ($filterGroups as $groups) {
            $or = $groups['or'];
            $filters = $groups['filters'];

            foreach ($filters as $filter) {
                $this->applyFilter($queryBuilder, $filter, $or);
            }
        }
    }

    protected function applyWithouGlobalScopes(Builder $queryBuilder, array $exludeGlobalScopes = [])
    {
        $queryBuilder->withoutGlobalScopes($exludeGlobalScopes);
    }
    
    protected function applyFilter(Builder $queryBuilder, array $filter, $or = false)
    {
        $column = $filter['column'];
        $method = ($or == true) ? 'orWhere' : 'where';
        $operator = $filter['operator'] ?? 'eq';
        $value = $filter['value'];
        $not = $filter['not'] ?? null;
        
        $whiteListFilter = (get_class_vars(get_class($queryBuilder->getModel()))['whiteListFilter']) ?? [];

        if (!in_array($column, $whiteListFilter)) {
            throw new InvalidArgumentException('Oops! You cannot filter column '. $column. '.');
        }

        $lastColumn = explode('.', $column);
        $lastColumn = end($lastColumn);
        $relations = str_replace('.'. $lastColumn, '', $column);
        
        switch ($operator) {
            // String contains
            case 'ct':
                $operator = 'like';
                $value = "%$value%";
                break;

            // Starts with
            case 'sw':
                $operator = 'like';
                $value = "$value%";
                break;
                
             // Ends with
             case 'ew':
                $operator = 'like';
                $value = "%$value";
                break;

            // Equals
            case 'eq':
                $operator = $not ? '!=' : '=';
                break;

            // Greater than
            case 'gt':
                $operator = $not ? '<' : '>';
                break;

            // Greater than or equalTo
            case 'gte':
                $operator = $not ? '<' : '>=';
                break;

            // Lesser than or equalTo
            case 'lte':
                $operator = $not ? '>' : '<=';
                break;

            // Lesser than
            case 'lt':
                $operator = $not ? '>' : '<';
                break;

            // In array
            case 'in':
                if ($or == true) {
                    $method = 'or-in';
                } else {
                    $method = 'in';
                }
                break;
            
            // Between
            case 'bt':
                if ($or == true) {
                    $method = 'or-bt';
                } else {
                    $method = 'bt';
                }
                break;
        }

        $customFilterMethod = $this->hasCustomMethod('filter', $column);
        if ($customFilterMethod) {
            return call_user_func_array([$this, 'filter'. $column], array($queryBuilder, $method, $operator, $value));
        }

        switch ($method) {
            case 'where':
                if (stripos($column, '.')) {
                    $queryBuilder->whereHas($relations, function ($q) use ($lastColumn, $method, $operator, $value) {
                        $q->where($lastColumn, $operator, $value);
                    });
                } else {
                    $queryBuilder->where($column, $operator, $value);
                }
                break;
        
            case 'orWhere':
                if (stripos($column, '.')) {
                    $queryBuilder->orWhereHas($relations, function ($q) use ($lastColumn, $method, $operator, $value) {
                        $q->where($lastColumn, $operator, $value);
                    });
                } else {
                    $queryBuilder->orWhere($column, $operator, $value);
                }
                break;
                
            case 'in':
                if (stripos($column, '.')) {
                    $queryBuilder->whereHas($relations, function ($q) use ($lastColumn, $method, $operator, $value) {
                        $q->whereIn($lastColumn, $value);
                    });
                } else {
                    $queryBuilder->whereIn($column, $value);
                }
                break;

            case 'or-in':
                if (stripos($column, '.')) {
                    $queryBuilder->orWhereHas($relations, function ($q) use ($lastColumn, $method, $operator, $value) {
                        $q->whereIn($lastColumn, $value);
                    });
                } else {
                    $queryBuilder->orWhereIn($column, $value);
                }
                break;
                
            case 'or-bt':
                if (stripos($column, '.')) {
                    $queryBuilder->orWhereHas($relations, function ($q) use ($lastColumn, $method, $operator, $value) {
                        $q->whereBetween($lastColumn, $value);
                    });
                } else {
                    $queryBuilder->orWhereBetween($column, $value);
                }
                break;
                
            case 'bt':
                if (stripos($column, '.')) {
                    $queryBuilder->whereHas($relations, function ($q) use ($lastColumn, $method, $operator, $value) {
                        $q->whereBetween($lastColumn, $value);
                    });
                } else {
                    $queryBuilder->whereBetween($column, $value);
                }
                break;
        }
    }

    protected function applySorting(Builder $queryBuilder, array $sorting)
    {
        foreach ($sorting as $sortRule) {
            if (is_array($sortRule)) {
                $key = $sortRule['key'];
                $direction = mb_strtolower($sortRule['direction']) === 'asc' ? 'ASC' : 'DESC';
            } else {
                $key = $sortRule;
                $direction = 'ASC';
            }

            $customSortMethod = $this->hasCustomMethod('sort', $key);
            if ($customSortMethod) {
                call_user_func([$this, $customSortMethod], $queryBuilder, $direction);
            } else {
                $queryBuilder->orderBy($key, $direction);
            }
        }
    }

    private function hasCustomMethod($type, $key)
    {
        $methodName = sprintf('%s%s', $type, Str::studly($key));
        if (method_exists($this, $methodName)) {
            return $methodName;
        }

        return false;
    }
}
