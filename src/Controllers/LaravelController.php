<?php

namespace one2tek\larapi\Controllers;

use JsonSerializable;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use one2tek\larapi\Core\Architect;
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
     *
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
     * Parse data using architect.
     *
     * @param  mixed  $data
     * @param  array  $options
     * @param  string  $key
     *
     * @return mixed
     */
    protected function parseData($data, array $options, $key = null)
    {
        $architect = new Architect();

        return $architect->parseData($data, $options['modes'], $key);
    }

    /**
     * Parse sort.
     *
     * @param  array  $sort
     *
     * @return array
     */
    protected function parseSort(array $sort)
    {
        return array_map(function ($sort) {
            if (!isset($sort['direction'])) {
                $sort['direction'] = 'asc';
            }

            return $sort;
        }, $sort);
    }

    /**
     * Parse sort by asc.
     *
     * @param  array|string  $sortByAsc
     *
     * @return array
     */
    protected function parseSortByAsc($sortByAsc)
    {
        if (is_null($sortByAsc)) {
            return [];
        }

        if (is_array($sortByAsc)) {
            return [];
        }
        
        return explode(',', $sortByAsc);
    }

    /**
     * Parse sort by desc.
     *
     * @param  array|string  $sortByDesc
     *
     * @return array
     */
    protected function parseSortByDesc($sortByDesc)
    {
        if (is_null($sortByDesc)) {
            return [];
        }

        if (is_array($sortByDesc)) {
            return [];
        }
        
        return explode(',', $sortByDesc);
    }

    /**
     * Parse selects.
     *
     * @param  string|array  $selects
     * @return array
     */
    protected function parseSelects($selects)
    {
        if (is_null($selects)) {
            return null;
        }

        $return = [];
        
        if (is_array($selects)) {
            foreach ($selects as $select) {
                $allSelects = explode(',', $select);
                foreach ($allSelects as $select) {
                    $return[] = $select;
                }
            }

            return $return;
        }
        
        return explode(',', $selects);
    }

    /**
     * Parse include.
     *
     * @param  string|array  $include
     *
     * @return array
     */
    protected function parseInclude($include)
    {
        if (is_string($include) && is_null($include)) {
            return [];
        }

        if (is_array($include) && !count($include)) {
            return [];
        }
        
        return explode(';', $include);
    }

    /**
     * Parse includes.
     *
     * @param  array  $includes
     * @return array
     */
    protected function parseIncludes(array $includes)
    {
        $return = [];

        foreach ($includes as $include) {
            $return[] = $include;
        }

        return $return;
    }

    /**
     * Parse modes.
     *
     * @param  array  $modeIds
     * @param  array  $modeSideload
     * @return array
     */
    protected function parseModes(array $modeIds, array $modeSideload)
    {
        $return = [];

        foreach ($modeIds as $mode1) {
            $return[$mode1] = 'ids';
        }

        foreach ($modeSideload as $mode2) {
            $return[$mode2] = 'sideload';
        }

        return $return;
    }

    /**
     * Parse withCount into resource.
     *
     * @param  array  $withCounts
     *
     * @return array
     */
    protected function parseWithCount(array $withCounts)
    {
        $return = [];

        foreach ($withCounts as $withCount) {
            $return[] = $withCount;
        }

        return $return;
    }
    
    /**
    * Parse excludeGlobalScopes into resource.
    *
    * @param  array  $excludeGlobalScopes
    *
    * @return array
    */
    protected function parseExcludeGlobalScopes(array $excludeGlobalScopes)
    {
        $return = [];

        foreach ($excludeGlobalScopes as $exludeGlobalScope) {
            $return[] = $exludeGlobalScope;
        }

        return $return;
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

        foreach ($filters as $column => $part) {
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

            if (!in_array($operator, $allowedOperators, )) {
                throw new LarapiException('Operator '. $operator. ' is not supported.');
            }

            if (is_array($part)) {
                $not = ($arrayCountValues == 2) ? key($part[$operator]) : false;
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
     *
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
            'selects' => [],
            'select' => [],
            'includes' => [],
            'include' => [],
            'withCount' => [],
            'has' => [],
            'doesntHave' => [],
            'excludeGlobalScopes' => [],
            'scope' => [],
            'sort' => [],
            'limit' => null,
            'page' => null,
            'mode' => 'embed',
            'filter_groups' => [],
            'filterByAnd' => [],
            'filterByOr' => [],
            'searchByAnd' => [],
            'searchByOr' => [],
            'append' => [],
            'sortByDesc' => [],
            'sortByAsc' => [],
        ], $this->defaults);

        $selects = $this->parseSelects($request->get('selects', $this->defaults['selects']));
        $select = $this->parseSelects($request->get('select', $this->defaults['select']));
        $includes = $this->parseIncludes($request->get('includes', $this->defaults['includes']));
        $include = $this->parseInclude($request->get('include', $this->defaults['include']));
        $modes = $this->parseModes($request->get('modeIds', []), $request->get('modeSideload', []));
        $withCount = $this->parseWithCount($request->get('withCount', $this->defaults['withCount']));
        $has = $request->get('has', $this->defaults['has']);
        $doesntHave = $request->get('doesntHave', $this->defaults['doesntHave']);
        $excludeGlobalScopes = $this->parseExcludeGlobalScopes($request->get('excludeGlobalScopes', $this->defaults['excludeGlobalScopes']));
        $scope = $request->get('scope', $this->defaults['scope']);
        $sort = $this->parseSort($request->get('sort', $this->defaults['sort']));
        $limit = $request->get('limit', $this->defaults['limit']);
        $page = $request->get('page', $this->defaults['page']);
        $filter_groups = $this->parseFilterGroups($request->get('filter_groups', $this->defaults['filter_groups']));
        $filterByAnd = $this->parseFilters($request->get('filter', $this->defaults['filterByAnd']));
        $filterByOr = $this->parseFilters($request->get('filterByOr', $this->defaults['filterByOr']), true);
        $searchByAnd = $this->parseFilters($request->get('search', $this->defaults['searchByAnd']), false, 'ct');
        $searchByOr = $this->parseFilters($request->get('searchByOr', $this->defaults['searchByOr']), true, 'ct');
        $append = $request->get('append', $this->defaults['append']);
        $sortByDesc = $this->parseSortByDesc($request->get('sortByDesc', $this->defaults['sortByDesc']));
        $sortByAsc = $this->parseSortByAsc($request->get('sortByAsc', $this->defaults['sortByAsc']));

        $data = [
            'select' => $select,
            'selects' => $selects,
            'includes' => $includes,
            'include' => $include,
            'withCount' => $withCount,
            'has' => $has,
            'doesntHave' => $doesntHave,
            'excludeGlobalScopes' => $excludeGlobalScopes,
            'modes' => $modes,
            'scope' => $scope,
            'sort' => $sort,
            'limit' => $limit,
            'page' => $page,
            'filter_groups' => $filter_groups,
            'filterByAnd' => $filterByAnd,
            'filterByOr' => $filterByOr,
            'searchByAnd' => $searchByAnd,
            'searchByOr' => $searchByOr,
            'append' => $append,
            'sortByDesc' => $sortByDesc,
            'sortByAsc' => $sortByAsc
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
    }
}
