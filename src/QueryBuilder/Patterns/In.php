<?php

namespace Osi\QueryBuilder\Patterns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

/**
 * Class In
 * @package QueryBuilder\Patterns
 */
class In implements Pattern
{
    const RULES = [
        'value'   => 'required|array',
        'not'     => 'boolean'
    ];

    /**
     * @param string $field
     * @param array $params
     * @param Builder $queryBuilder
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function apply(string $field, array $params, Builder $queryBuilder): void
    {
        if (is_string($params['value'] ?? false)) {
            $params['value'] = explode(",", $params['value']);
        }

        Validator::validate($params, self::RULES);

        $queryBuilder->whereIn($field, $params['value'], "and", $params['not'] ?? false);
    }
}
