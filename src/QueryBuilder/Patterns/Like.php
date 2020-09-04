<?php

namespace QueryBuilder\Patterns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

/**
 * Class Like
 * @package QueryBuilder\Patterns
 */
class Like implements Pattern
{
    const PATTERN = 'LIKE';
    const REVERSE_PATTERN = 'NOT LIKE';

    const RULES = [
        'value'   => 'required',
        'pattern' => 'required|in:LIKE',
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
        $pattern = self::PATTERN;
        Validator::validate($params, self::RULES);
        if ($params['not'] ?? false) {
            $pattern = self::REVERSE_PATTERN;
        }
        $queryBuilder->where($field, $pattern, "%" . $params['value'] . "%");
    }
}
