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
            $this->applySelects($queryBuilder, $selects);
        }
        
        if (isset($select) && $select) {
            $this->applySelects($queryBuilder, $select);
        }

        if (isset($includes)) {
            $this->applyWith($queryBuilder, $includes);
        }

        if (isset($include)) {
            $this->applyWith($queryBuilder, $include);
        }
        
        if (isset($withCount)) {
            $this->applyWithCount($queryBuilder, $withCount);
        }
        
        if (isset($has)) {
            $this->applyHas($queryBuilder, $has);
        }

        if (isset($doesntHave)) {
            $this->applyDoesntHave($queryBuilder, $doesntHave);
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

        if (isset($limit)) {
            $queryBuilder->limit($limit);
        }

        if (isset($page)) {
            $queryBuilder->offset(($page > 0 ? $page - 1 : 0) * $limit);
        }

        if (isset($sortByAsc)) {
            $this->applySortByAsc($queryBuilder, $sortByAsc);
        }

        if (isset($sortByDesc)) {
            $this->applySortByDesc($queryBuilder, $sortByDesc);
        }

        if (isset($orderByRandom) && $orderByRandom) {
            $this->applyOrderByRandom($queryBuilder, $orderByRandom);
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

    protected function applySelects(Builder $queryBuilder, array $fields = [])
    {
        $queryBuilder->select($fields);
    }

    protected function applyWith(Builder $queryBuilder, array $withs = [])
    {
        $queryBuilder->with($withs);
    }

    protected function applyWithCount(Builder $queryBuilder, array $withCount = [])
    {
        $queryBuilder->withCount($withCount);
    }

    protected function applyOrderByRandom(Builder $queryBuilder, bool $orderByRaw)
    {
        $queryBuilder->orderByRaw('RAND()');
    }

    protected function applyHas(Builder $queryBuilder, array $relations = [])
    {
        foreach ($relations as $relation) {
            $queryBuilder->has($relation);
        }
    }

    protected function applyDoesntHave(Builder $queryBuilder, array $relations = [])
    {
        foreach ($relations as $relation) {
            $queryBuilder->doesntHave($relation);
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
        $wantsRelationship = stripos($column, '.');
        $clauseOperator = true;
        $lastColumn = explode('.', $column);
        $lastColumn = end($lastColumn);
        $relationName = str_replace('.'. $lastColumn, '', $column);
        $filterRawJoinColumns = isset($this->filterRawJoinColumns) ? $this->filterRawJoinColumns : [];

        $this->checkFilterColumn($column, get_class($queryBuilder->getModel()));

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

    private function checkFilterColumn(String $column, String $baseClassName, array $overrideWhiteListFilter = null)
    {
        if (empty($column) || empty($baseClassName)) {
            return;
        }

        // Retrieve the whiteListFilter
        $whiteListFilter = $overrideWhiteListFilter ?? ((array)(get_class_vars($baseClassName)['whiteListFilter']) ?? []);

        // Check if the whitelist filter is a star
        if (in_array('*', $whiteListFilter)) {
            if (count($whiteListFilter) > 1) {
                throw new LarapiException('Oops! If you use "*" for the whiteListFilter, you cannot specify another column on ' .  $baseClassName . ' class.');
            }
            return;
        }

        // Check if full column can filered.
        if (in_array($column, $whiteListFilter)) {
            return;
        }

        $parts = explode('.', $column);
        $firstPart = $parts[0];

        $simpleColumnCheckInListFilter = in_array($firstPart, $whiteListFilter);
        $complexColumnCheckInListFilter = in_array($firstPart . '.*', $whiteListFilter);

        // Check if splitted column can filered.
        if (!$simpleColumnCheckInListFilter && !$complexColumnCheckInListFilter) {
            throw new LarapiException('Oops! You cannot filter column ' . $column . ' on ' .  $baseClassName . ' class.');
        }

        // Get next part and next class
        $nextColums = join('.', array_slice($parts, 1));
        $baseClass = new $baseClassName();
        $nextClass = method_exists($baseClass, $firstPart) ? get_class($baseClass->$firstPart()->getRelated()) : '';
        
        // If the whiteListFilter contains a column with a star we want to bypass the check for the next part
        $nextOverrideWhiteListFilter = $complexColumnCheckInListFilter ? ['*'] : null;

        // Recursive call to check sub parts
        $this->checkFilterColumn($nextColums, $nextClass, $nextOverrideWhiteListFilter);
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
