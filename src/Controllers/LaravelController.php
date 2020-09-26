<?php

namespace one2tek\larapi\Controllers;

use JsonSerializable;
use InvalidArgumentException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use one2tek\larapi\Core\Architect;
use Illuminate\Http\Request;

abstract class LaravelController extends Controller
{
    /**
     * Defaults
     * @var array
     */
    protected $defaults = [];

    /**
     * Create a json response.
     *
     * @param  mixed  $data
     * @param  integer $statusCode
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
     * @param  mixed $data
     * @param  array  $options
     * @param  string $key
     *
     * @return mixed
     */
    protected function parseData($data, array $options, $key = null)
    {
        $architect = new Architect();

        return $architect->parseData($data, $options['modes'], $key);
    }

    /**
     * Pare sort.
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
     * Pare selects.
     *
     * @param  array  $selects
     *
     * @return array
     */
    protected function parseSelects(array $selects)
    {
        if (count($selects)) {
            return explode(',', $selects[0]);
        }

        return [];
    }

    /**
     * Parse include strings into resource and modes.
     *
     * @param  array  $includes
     *
     * @return array The parsed resources and their respective modes
     */
    protected function parseIncludes(array $includes)
    {
        $return = [
            'includes' => [],
            'modes' => []
        ];

        foreach ($includes as $include) {
            $explode = explode('-', $include);
            $explode2 = explode(':', $include);

            if (!isset($explode[1])) {
                $explode[1] = $this->defaults['mode'];
            }

            $return['includes'][] = $explode[0];

            if (strpos($include, ':') !== false) {
                $return['modes'][$explode2[0]] = $explode[1];
            } else {
                $return['modes'][$explode[0]] = $explode[1];
            }
        }

        return $return;
    }

    /**
     * Parse withCount into resource.
     *
     * @param  array  $withCounts
     *
     * @return array The parsed resources
     */
    protected function parseWithCount(array $withCounts)
    {
        $return = [
            'withCount' => []
        ];

        foreach ($withCounts as $withCount) {
            $explode = explode(':', $withCount);

            $return['withCount'][] = $explode[0];
        }

        return $return;
    }
    
    /**
    * Parse exludeGlobalScopes into resource.
    *
    * @param  array  $exludeGlobalScopes
    *
    * @return array The parsed resources
    */
    protected function parseExludeGlobalScopes(array $exludeGlobalScopes)
    {
        $return = [
            'exludeGlobalScopes' => []
        ];

        foreach ($exludeGlobalScopes as $exludeGlobalScope) {
            $explode = explode(':', $exludeGlobalScope);

            $return['exludeGlobalScopes'][] = $explode[0];
        }

        return $return;
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
                throw new InvalidArgumentException('Filter group does not have the \'filters\' key.');
            }
            $filters = array_map(function ($filter) use ($keysNeeded) {
                if (count(array_intersect_key(array_flip($keysNeeded), $filter)) != count($keysNeeded)) {
                    throw new InvalidArgumentException('You need to pass column, operator and value in filters.');
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
            'includes' => [],
            'withCount' => [],
            'withs' => [],
            'has' => [],
            'exludeGlobalScopes' => [],
            'scope' => [],
            'sort' => [],
            'limit' => null,
            'page' => null,
            'mode' => 'embed',
            'filter_groups' => [],
            'append' => [],
        ], $this->defaults);

        $selects = $this->parseSelects($request->get('select', $this->defaults['selects']));
        $includes = $this->parseIncludes($request->get('includes', $this->defaults['includes']));
        $withCount = $this->parseWithCount($request->get('withCount', $this->defaults['withCount']));
        $withs = $request->get('with', $this->defaults['withs']);
        $has = $request->get('has', $this->defaults['has']);
        $exludeGlobalScopes = $this->parseExludeGlobalScopes($request->get('exludeGlobalScopes', $this->defaults['exludeGlobalScopes']));
        $scope = $request->get('scope', $this->defaults['scope']);
        $sort = $this->parseSort($request->get('sort', $this->defaults['sort']));
        $limit = $request->get('limit', $this->defaults['limit']);
        $page = $request->get('page', $this->defaults['page']);
        $filter_groups = $this->parseFilterGroups($request->get('filter_groups', $this->defaults['filter_groups']));
        $append = $request->get('append', $this->defaults['append']);

        $data = [
            'selects' => $selects,
            'includes' => $includes['includes'],
            'withCount' => $withCount['withCount'],
            'withs' => $withs,
            'has' => $has,
            'exludeGlobalScopes' => $exludeGlobalScopes['exludeGlobalScopes'],
            'modes' => $includes['modes'],
            'scope' => $scope,
            'sort' => $sort,
            'limit' => $limit,
            'page' => $page,
            'filter_groups' => $filter_groups,
            'append' => $append
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
            throw new InvalidArgumentException('Cannot use page option without limit option.');
        }
    }
}
