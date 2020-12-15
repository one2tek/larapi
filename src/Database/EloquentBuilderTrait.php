<?php

namespace one2tek\larapi\Database;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use one2tek\larapi\Exceptions\LarapiException;

trait EloquentBuilderTrait
{
    protected function applyResourceOptions(Builder $queryBuilder, array $options = [])
    {
        if (empty($options)) {
            return $queryBuilder;
        }

        extract($options);

        if (isset($selects) && $selects) {
            $queryBuilder->select($selects);
        }
        if (isset($select) && $select) {
            $queryBuilder->select($select);
        }

        if (isset($includes)) {
            $queryBuilder->with($includes);
        }

        if (isset($include)) {
            if (count($include)) {
                $queryBuilder->with($include);
            }
        }
        
        if (isset($withCount)) {
            $queryBuilder->withCount($withCount);
        }
        
        if (isset($withs)) {
            foreach ($withs as $with) {
                $queryBuilder->with([$with['name'] => function ($query) use ($with) {
                    if (count($with['select'] ?? [])) {
                        $query->select($with['select']);
                    }

                    if (count($with['group_by'] ?? [])) {
                        $query->groupBy($with['group_by']);
                    }

                    if (count($with['sort'] ?? [])) {
                        foreach ($with['sort'] as $sort) {
                            $query->orderBy($sort['key'], $sort['direction']);
                        }
                    }
                }]);
            }
        }
        
        if (isset($has)) {
            foreach ($has as $relation) {
                $queryBuilder->has($relation);
            }
        }

        if (isset($doesntHave)) {
            foreach ($doesntHave as $relation) {
                $queryBuilder->doesntHave($relation);
            }
        }
        
        if (isset($excludeGlobalScopes)) {
            $this->applyWithouGlobalScopes($queryBuilder, $excludeGlobalScopes);
        }

        if (isset($filter_groups)) {
            $this->applyFilterGroups($queryBuilder, $filter_groups);
        }

        if (isset($filterByAnd)) {
            $this->applyFilterGroups($queryBuilder, $filterByAnd);
        }

        if (isset($filterByOr)) {
            $this->applyFilterGroups($queryBuilder, $filterByOr);
        }

        if (isset($searchByAnd)) {
            $this->applyFilterGroups($queryBuilder, $searchByAnd);
        }

        if (isset($searchByOr)) {
            $this->applyFilterGroups($queryBuilder, $searchByOr);
        }

        if (isset($sort)) {
            $this->applySorting($queryBuilder, $sort);
        }

        if (isset($limit)) {
            $queryBuilder->limit($limit);
        }

        if (isset($page)) {
            $queryBuilder->offset($page * $limit);
        }

        if (isset($distinct)) {
            $queryBuilder->distinct();
        }

        if (isset($sortByAsc) && $sortByAsc) {
            $this->applySortByAsc($queryBuilder, $sortByAsc);
        }

        if (isset($sortByDesc) && $sortByDesc) {
            $this->applySortByDesc($queryBuilder, $sortByDesc);
        }

        return $queryBuilder;
    }

    protected function applyFilterGroups(Builder $queryBuilder, array $filterGroups = [])
    {
        foreach ($filterGroups as $groups) {
            $or = $groups['or'];
            $filters = $groups['filters'];

            $queryBuilder->where(function (Builder $query) use ($filters, $or) {
                foreach ($filters as $filter) {
                    $this->applyFilter($query, $filter, $or);
                }
            });
        }
    }

    protected function applySortByAsc(Builder $queryBuilder, array $sortByAsc = [])
    {
        foreach ($sortByAsc as $sortByAscKey) {
            $customSortMethod = $this->hasCustomMethod('sort', $sortByAscKey);
            if ($customSortMethod) {
                call_user_func([$this, $customSortMethod], $queryBuilder, 'ASC');
            } else {
                $queryBuilder->orderBy($sortByAscKey);
            }
        }
    }

    protected function applySortByDesc(Builder $queryBuilder, array $sortByDesc = [])
    {
        foreach ($sortByDesc as $sortByDescKey) {
            $customSortMethod = $this->hasCustomMethod('sort', $sortByDescKey);
            if ($customSortMethod) {
                call_user_func([$this, $customSortMethod], $queryBuilder, 'DESC');
            } else {
                $queryBuilder->orderByDesc($sortByDescKey);
            }
        }
    }

    protected function applyWithouGlobalScopes(Builder $queryBuilder, array $excludeGlobalScopes = [])
    {
        $queryBuilder->withoutGlobalScopes($excludeGlobalScopes);
    }
    
    protected function applyFilter(Builder $queryBuilder, array $filter, $or = false)
    {
        $column = $filter['column'];
        $method = 'where';
        $operator = $filter['operator'] ?? 'eq';
        $value = $filter['value'];
        $not = $filter['not'] ?? false;
        $whiteListFilter = (get_class_vars(get_class($queryBuilder->getModel()))['whiteListFilter']) ?? [];
        $wantsRelationship = stripos($column, '.');
        $clauseOperator = true;
        $lastColumn = explode('.', $column);
        $lastColumn = end($lastColumn);
        $relationName = str_replace('.'. $lastColumn, '', $column);
        $filterRawJoinColumns = isset($this->filterRawJoinColumns) ? $this->filterRawJoinColumns : [];

        // Check if column can filered.
        if (!in_array($column, $whiteListFilter)) {
            throw new LarapiException('Oops! You cannot filter column '. $column. '.');
        }

        // Check operator.
        switch ($operator) {
            // String contains
            case 'ct':
                $operator = $not ? 'NOT LIKE' : 'LIKE';
                $value = "%$value%";
                break;

            // Starts with
            case 'sw':
                $operator = $not ? 'NOT LIKE' : 'LIKE';
                $value = "$value%";
                break;
                
             // Ends with
             case 'ew':
                $operator = $not ? 'NOT LIKE' : 'LIKE';
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
                $method = $not ? 'whereNotIn' : 'whereIn';
                $clauseOperator = false;
                break;
            
            // Between
            case 'bt':
                $method = $not ? 'whereNotBetween' : 'whereBetween';
                $clauseOperator = false;
                break;
        }

        // Support or operator.
        if ($or == true) {
            $method = 'or'. $method;
        }

        // Custom filter.
        $customFilterMethod = $this->hasCustomMethod('filter', $column);
        if ($customFilterMethod) {
            return call_user_func_array([$this, 'filter'. $column], array($queryBuilder, $method, $operator, $value, $clauseOperator, $or));
        }

        // Finally apply filter.
        if ($wantsRelationship && !in_array($column, $filterRawJoinColumns)) {
            // Remove or operator support.
            $method = str_replace('or', '', $method);

            $queryFunction = function ($q) use ($lastColumn, $operator, $value, $method, $clauseOperator) {
                if ($clauseOperator == false) {
                    $q->$method($lastColumn, $value);
                } else {
                    $q->$method($lastColumn, $operator, $value);
                }
            };

            if ($or == true) {
                $queryBuilder->orWhereHas($relationName, $queryFunction);
            } else {
                $queryBuilder->whereHas($relationName, $queryFunction);
            };
        } else {
            if ($clauseOperator == false) {
                $queryBuilder->$method($column, $value);
            } else {
                $queryBuilder->$method($column, $operator, $value);
            }
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
