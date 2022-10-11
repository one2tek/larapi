<?php

namespace one2tek\larapi\Controllers;

use JsonSerializable;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Arrayable;
use one2tek\larapi\Exceptions\LarapiException;

abstract class LaravelController extends Controller
{
    /**
     * Defaults.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * Create a json response.
     *
     * @param  mixed  $data
     * @param  integer  $statusCode
     * @param  array  $headers
     * @return Illuminate\Http\JsonResponse
     */
    protected function response($data, $statusCode = 200, array $headers = [])
    {
        if ($data instanceof Arrayable && !$data instanceof JsonSerializable) {
            $data = $data->toArray();
        }

        return new JsonResponse($data, $statusCode, $headers);
    }

    /**
     * Parse sort by asc.
     *
     * @param  string  $sortByAsc
     * @return array
     */
    protected function parseSortByAsc($sortByAsc)
    {
        if (is_null($sortByAsc)) {
            return [];
        }
        
        return explode(',', $sortByAsc);
    }

    /**
     * Parse sort by desc.
     *
     * @param  string  $sortByDesc
     * @return array
     */
    protected function parseSortByDesc($sortByDesc)
    {
        if (is_null($sortByDesc)) {
            return [];
        }
        
        return explode(',', $sortByDesc);
    }

    /**
     * Parse selects.
     *
     * @param  string  $selects
     * @return array
     */
    protected function parseSelects($selects)
    {
        if (is_null($selects)) {
            return [];
        }
        
        return explode(',', $selects);
    }

    /**
     * Parse appends.
     *
     * @param  string  $appends
     * @return array
     */
    protected function parseAppends($appends)
    {
        if (is_null($appends)) {
            return [];
        }
        
        return explode(',', $appends);
    }

    /**
     * Parse includes.
     *
     * @param  string  $includes
     * @return array
     */
    protected function parseIncludes($includes)
    {
        if (is_null($includes)) {
            return [];
        }
        
        return explode(';', $includes);
    }

    /**
     * Parse with counts into resource.
     *
     * @param  string  $withCounts
     * @return array
     */
    protected function parseWithCounts($withCounts)
    {
        if (is_null($withCounts)) {
            return [];
        }
        
        return explode(',', $withCounts);
    }

    /**
     * Parse has into resource.
     *
     * @param  string  $has
     * @return array
     */
    protected function parseHas($has)
    {
        if (is_null($has)) {
            return [];
        }
        
        return explode(',', $has);
    }

    /**
     * Parse doesnt have into resource.
     *
     * @param  string  $has
     * @return array
     */
    protected function parseDoesntHave($doesntHave)
    {
        if (is_null($doesntHave)) {
            return [];
        }
        
        return explode(',', $doesntHave);
    }
    
    /**
     * Parse exclude global scopes into resource.
     *
     * @param  string  $excludeGlobalScopes
     * @return array
     */
    protected function parseExcludeGlobalScopes($excludeGlobalScopes)
    {
        if (is_null($excludeGlobalScopes)) {
            return [];
        }
        
        return explode(',', $excludeGlobalScopes);
    }

    /**
     * Parse scopes into resource.
     *
     * @param  string  $scopes
     * @return array
     */
    protected function parseScopes($scopes)
    {
        if (is_null($scopes)) {
            return [];
        }
        
        return explode(',', $scopes);
    }

    /**
     * Parse order by random.
     *
     * @param  string  $value
     * @return bool
     */
    protected function parseOrderByRandom($value)
    {
        $value = $value ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : false;

        return $value;
    }

    /**
     * Parse with trashed.
     *
     * @param  string  $value
     * @return bool
     */
    protected function parseWithTrashed($value)
    {
        $value = $value ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : false;

        return $value;
    }

    /**
     * Parse filters.
     *
     * @param  array  $filter
     * @param  bool  $or
     * @return array
     */
    protected function parseFilters(array $filters, $or = false, $defaultOperator = 'eq')
    {
        if (!count($filters)) {
            return [];
        }

        $parsedFilters = [];
        $allowedOperators = [
            'ct',
            'sw',
            'ew',
            'eq',
            'gt',
            'gte',
            'lte',
            'lt',
            'in',
            'bt',
        ];

        foreach ($filters as $indexOrColumn => $part) {
            if (is_numeric($indexOrColumn)) {
                $column = array_key_first($part);
                $part = $part[$column];
            } else {
                $column = $indexOrColumn;
            }

            $arrayCountValues = is_array($part) ? count($part, COUNT_RECURSIVE) : 0;
            $operator = $defaultOperator;
            $not = false;
            $value = (is_array($part)) ? null : $part;
            if (is_array($part)) {
                $operator = key($part) ?? $defaultOperator;
            }
            
            if ($arrayCountValues > 2) {
                throw new LarapiException('Filter is not well formed.');
            }

            if (!in_array($operator, $allowedOperators)) {
                throw new LarapiException('Operator '. $operator. ' is not supported.');
            }

            if (is_array($part)) {
                $not = ($arrayCountValues == 2) ? key($part[$operator]) : false;
                $not = $not === 'not' ? true : $not;
                $not = $not ? filter_var($not, FILTER_VALIDATE_BOOLEAN) : false;
            
                $value = $part[(string)key($part)];
                $value = is_array($value) ? array_shift($value) : $value;
                $value = (Str::contains($value, ',')) ? explode(',', $value) : $value;
            }

            $parsedFilters[] = [
                'column' => $column,
                'operator' => $operator,
                'not' => $not,
                'value' => $value
            ];
        }
        
        return [
            [
                'filters' => $parsedFilters,
                'or' => $or
            ]
        ];
    }

    /**
     * Parse filter group strings into filters.
     *
     * @param  array  $filter_groups
     * @return array
     */
    protected function parseFilterGroups(array $filter_groups)
    {
        $return = [];

        $keysNeeded = ['column', 'operator', 'value'];
        foreach ($filter_groups as $group) {
            if (!array_key_exists('filters', $group)) {
                throw new LarapiException('Filter group does not have the \'filters\' key.');
            }
            $filters = array_map(function ($filter) use ($keysNeeded) {
                if (count(array_intersect_key(array_flip($keysNeeded), $filter)) != count($keysNeeded)) {
                    throw new LarapiException('You need to pass column, operator and value in filters.');
                }
                
                if (($filter['operator'] == 'in') && (!is_array($filter['value']))) {
                    throw new LarapiException('You need to make value as array because you are using \'in\' operator.');
                }

                if (($filter['operator'] == 'bt') && (!is_array($filter['value']))) {
                    throw new LarapiException('You need to make value as array because you are using \'bt\' operator.');
                }

                if (!isset($filter['not'])) {
                    $filter['not'] = false;
                }
                return $filter;
            }, $group['filters']);

            $return[] = [
                'filters' => $filters,
                'or' => isset($group['or']) ? $group['or'] : false
            ];
        }

        return $return;
    }

    /**
     * Parse GET parameters into resource options.
     *
     * @return array
     */
    protected function parseResourceOptions($request = null)
    {
        if ($request === null) {
            $request = request();
        }

        $this->defaults = array_merge([
            'selects' => null,
            'select' => null,
            'includes' => null,
            'include' => null,
            'withCount' => null,
            'has' => null,
            'doesntHave' => null,
            'excludeGlobalScopes' => null,
            'scope' => null,
            'limit' => null,
            'page' => null,
            'filter_groups' => [],
            'filterByAnd' => [],
            'filterByOr' => [],
            'searchByAnd' => [],
            'searchByOr' => [],
            'append' => null,
            'sortByDesc' => null,
            'sortByAsc' => null,
            'orderByRandom' => false,
            'withTrashed' => false
        ], $this->defaults);

        $selects = $this->parseSelects($request->get('selects', $this->defaults['selects']));
        $select = $this->parseSelects($request->get('select', $this->defaults['select']));
        $includes = $this->parseIncludes($request->get('includes', $this->defaults['includes']));
        $include = $this->parseIncludes($request->get('include', $this->defaults['include']));
        $withCount = $this->parseWithCounts($request->get('withCount', $this->defaults['withCount']));
        $has = $this->parseHas($request->get('has', $this->defaults['has']));
        $doesntHave = $this->parseDoesntHave($request->get('doesntHave', $this->defaults['doesntHave']));
        $excludeGlobalScopes = $this->parseExcludeGlobalScopes($request->get('excludeGlobalScopes', $this->defaults['excludeGlobalScopes']));
        $scope = $this->parseScopes($request->get('scope', $this->defaults['scope']));
        $limit = $request->get('limit', $this->defaults['limit']);
        $page = $request->get('page', $this->defaults['page']);
        $filter_groups = $this->parseFilterGroups($request->get('filter_groups', $this->defaults['filter_groups']));
        $filterByAnd = $this->parseFilters($request->get('filter', $this->defaults['filterByAnd']));
        $filterByOr = $this->parseFilters($request->get('filterByOr', $this->defaults['filterByOr']), true);
        $searchByAnd = $this->parseFilters($request->get('search', $this->defaults['searchByAnd']), false, 'ct');
        $searchByOr = $this->parseFilters($request->get('searchByOr', $this->defaults['searchByOr']), true, 'ct');
        $append = $this->parseAppends($request->get('append', $this->defaults['append']));
        $sortByDesc = $this->parseSortByDesc($request->get('sortByDesc', $this->defaults['sortByDesc']));
        $sortByAsc = $this->parseSortByAsc($request->get('sortByAsc', $this->defaults['sortByAsc']));
        $orderByRandom = $this->parseOrderByRandom($request->get('orderByRandom', $this->defaults['orderByRandom']));
        $withTrashed = $this->parseWithTrashed($request->get('withTrashed', $this->defaults['withTrashed']));

        $data = [
            'select' => $select,
            'selects' => $selects,
            'includes' => $includes,
            'include' => $include,
            'withCount' => $withCount,
            'has' => $has,
            'doesntHave' => $doesntHave,
            'excludeGlobalScopes' => $excludeGlobalScopes,
            'scope' => $scope,
            'limit' => $limit,
            'page' => $page,
            'filter_groups' => $filter_groups,
            'filterByAnd' => $filterByAnd,
            'filterByOr' => $filterByOr,
            'searchByAnd' => $searchByAnd,
            'searchByOr' => $searchByOr,
            'append' => $append,
            'sortByDesc' => $sortByDesc,
            'sortByAsc' => $sortByAsc,
            'orderByRandom' => $orderByRandom,
            'withTrashed' => $withTrashed
        ];

        $this->validateResourceOptions($data);

        return $data;
    }

    /**
     * Validate resource options.
     */
    private function validateResourceOptions(array $data)
    {
        if ($data['page'] !== null && $data['limit'] === null) {
            throw new LarapiException('Cannot use page option without limit option.');
        }

        if (!is_null($data['page'])) {
            if (!is_int((int)$data['page'])) {
                throw new LarapiException('Page need to be int.');
            }

            if ($data['page'] == 0) {
                throw new LarapiException('Page need to start from 1.');
            }
        }

        if (!is_null($data['limit'])) {
            if (!is_int((int)$data['limit'])) {
                throw new LarapiException('Limit need to be int.');
            }
        }
    }
}
