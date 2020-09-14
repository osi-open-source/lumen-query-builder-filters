<?php

namespace Osi\QueryBuilder\Patterns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Interface Pattern
 * @package QueryBuilder\Patterns
 */
interface Pattern
{

    /**
     * @param string $field
     * @param array $params
     * @param Builder $queryBuilder
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function apply(string $field, array $params, Builder $queryBuilder): void;
}
