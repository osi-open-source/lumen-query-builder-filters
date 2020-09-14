<?php

namespace Osi\QueryBuilder\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Class SearchRequest
 * @package App\Http\Middleware
 */
class SearchRequest
{

    const ITEMS_PER_PAGE = 50;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->hasValidRequest($request)) {
            return $next($request);
        }
        return $next($this->getSearchCriteriaRequest($request));
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function hasValidRequest(Request $request): bool
    {
        if (!$request->filled('filters')) {
            return false;
        }
        if (!is_string($request->get('filters'))) {
            return false;
        }
        return true;
    }

    /**
     * @param Request $request
     * @return Request
     */
    private function getSearchCriteriaRequest(Request $request): Request
    {
        $filters = json_decode($request->get('filters'), true);
        if (empty($filters) || !is_array($filters)) {
            return $request;
        }

        $request->request->remove('filters');
        $request->query->remove('filters');
        $request->merge(Arr::except($filters, ['take', 'skip', 'page', 'with', 'sort']));
        $request->offsetSet('perPage', $perPage = (int) ($filters['take'] ?? $filters['perPage'] ?? $request->get('perPage', self::ITEMS_PER_PAGE)));
        if (isset($filters['skip'])) {
            $page = (int)floor(((int)$filters['skip']) / $perPage) + 1;
            if ($page > 1) {
                $request->offsetSet('page', $page);
            }
        } elseif (isset($filters['page'])) {
            $request->offsetSet('page', (int)$filters['page']);
        }
        if (isset($filters['sort'])) {
            $request->offsetSet('order_by', $filters['sort']);
        }
        if (isset($filters['with'])) {
            $request->offsetSet('with', $filters['with']);
        }
        return $request;
    }
}
