<?php

namespace Osi\QueryBuilder\Patterns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class NullPattern
 * @package Osi\QueryBuilder\Patterns
 */
class NullPattern implements Pattern
{
    /**
     * @param string $field
     * @param array $params
     * @param Builder $queryBuilder
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function apply(string $field, array $params, Builder $queryBuilder): void
    {
        $queryBuilder->whereNull($field, "and", $params['not'] ?? false);
    }
}
