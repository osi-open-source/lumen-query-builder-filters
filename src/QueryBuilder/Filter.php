<?php

namespace QueryBuilder\Filters;

use QueryBuilder\Patterns\Between;
use QueryBuilder\Patterns\In;
use QueryBuilder\Patterns\Like;
use QueryBuilder\Patterns\NullPattern;
use QueryBuilder\Patterns\Pattern;
use QueryBuilder\Patterns\Where;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

/**
 * Class Filter
 * @package QueryBuilder\Filters
 */
class Filter
{
    public const PAGE = "page";
    public const PER_PAGE = "perPage";
    public const WITH = "with";
    public const WHERE_VALUE = "value";
    public const WHERE_PATTERN = "pattern";
    public const WHERE_NOT = "not";
    public const ORDER_BY = "order_by";
    public const ORDER_NAME = "name";
    public const ORDER_DIRECTION = "direction";

    public const DEFAULT_PATTERNS = [
        '='       => Where::class,
        '!='      => Where::class,
        '>'       => Where::class,
        '<'       => Where::class,
        '>='      => Where::class,
        '<='      => Where::class,
        'LIKE'    => Like::class,
        'BETWEEN' => Between::class,
        'IN'      => In::class,
        'NULL'    => NullPattern::class,
    ];

    private $patterns;

    /**
     * Filter constructor.
     * @param array $patterns
     */
    public function __construct(array $patterns = self::DEFAULT_PATTERNS)
    {
        $this->patterns = $patterns;
    }

    /**
     * Apply condition on query builder based on search criteria
     *
     * @param array $searchCriteria
     * @param Builder $queryBuilder
     * @return Builder
     * @throws Exception
     */
    public function where(array $searchCriteria, Builder $queryBuilder): Builder
    {
        //skip pagination related query params
        $searchCriteria = Arr::except($searchCriteria, [self::PAGE, self::PER_PAGE, self::ORDER_BY, self::WITH]);
        foreach ($searchCriteria as $key => $value) {
            if (is_array($value)) {
                $this->applyArrayTerm($key, $value, $queryBuilder);
            } else {
                $this->applySimpleTerm($key, $value, $queryBuilder);
            }
        }
        return $queryBuilder;
    }

    /**
     * @param string $key
     * @param string $value
     * @param Builder $queryBuilder
     * @return void
     */
    private function applySimpleTerm(string $key, string $value, Builder $queryBuilder): void
    {
        //we can pass multiple params for a filter with commas
        $allValues = explode(',', $value ?? '');
        if (count($allValues) > 1) {
            $queryBuilder->whereIn($key, $allValues);
        } elseif ($value === null || $value == "NULL" || $value == "null") {
            $queryBuilder->whereNull($key);
        } else {
            $queryBuilder->where($key, "=", $value);
        }
    }

    /**
     * @param string $key
     * @param array $value
     * @param Builder $queryBuilder
     * @return void
     * @throws Exception
     */
    private function applyArrayTerm(string $key, array $value, Builder $queryBuilder): void
    {
        if (!Arr::isAssoc($value)) {
            $queryBuilder->whereIn($key, $value);
            return;
        }

        $value[self::WHERE_VALUE] = $value[self::WHERE_VALUE] ?? "";
        $value[self::WHERE_PATTERN] = $value[self::WHERE_PATTERN] ?? "=";

        $pattern = $this->getPattern($value[self::WHERE_PATTERN]);
        $pattern->apply($key, $value, $queryBuilder);
    }

    /**
     * @param array|string $orderBy
     * @param Builder $queryBuilder
     * @return Builder
     */
    public function order($orderBy, Builder $queryBuilder): Builder
    {
        if (is_string($orderBy)) {
            if (preg_match("/^(.*?)\s+(DESC|ASC)$/", $orderBy, $result)) {
                $queryBuilder->orderBy($result[1], $result[2]);
            } else {
                $queryBuilder->orderBy($orderBy);
            }
        } elseif (is_array($orderBy) && isset($orderBy[self::ORDER_NAME])) {
            $queryBuilder->orderBy($orderBy[self::ORDER_NAME], $orderBy[self::ORDER_DIRECTION] ?? "ASC");
        } elseif (is_array($orderBy)) {
            foreach ($orderBy as $element) {
                $this->order($element, $queryBuilder);
            }
        }
        return $queryBuilder;
    }

    /**
     * @param array $filters
     * @param Builder $queryBuilder
     * @param Closure|null $middleClosure
     * @return Builder
     */
    public function search(array $filters, Builder $queryBuilder, ?Closure $middleClosure = null): Builder
    {
        if (isset($filters[self::ORDER_BY])) {
            $this->order($filters[self::ORDER_BY], $queryBuilder);
        }
        if (isset($filters[self::WITH])) {
            $queryBuilder->with($filters[self::WITH]);
        }
        if ($middleClosure instanceof Closure) {
            $middleClosure($queryBuilder);
        }
        $queryBuilder->where(function ($query) use ($filters) {
            $this->where($filters, $query);
        });
        return $queryBuilder;
    }

    /**
     * @param string $patternKey
     * @return Pattern
     * @throws Exception
     */
    private function getPattern(string $patternKey): Pattern
    {
        if (!class_exists($this->patterns[$patternKey])) {
            throw new Exception('Pattern is not defined');
        }
        /** @var Pattern $pattern */
        $pattern = App::make($this->patterns[$patternKey]);
        if (!$pattern instanceof Pattern) {
            throw new Exception('Pattern is not defined');
        }
        return $pattern;
    }
}
