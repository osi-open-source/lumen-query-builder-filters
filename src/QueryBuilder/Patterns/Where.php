<?php

namespace Osi\QueryBuilder\Patterns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

/**
 * Class Where
 * @package QueryBuilder\Patterns
 */
class Where implements Pattern
{
    const REVERSE_PATTERNS = [
        '=' => '!=',
        '!=' => '=',
        '<' => '>',
        '>' => '<',
        '<=' => '>',
        '>=' => '<'
    ];

    const RULES = [
        'value' => 'required',
        'pattern' => 'required|in:=,!=,<,>,<=,>=',
        'not' => 'boolean'
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
        Validator::validate($params, self::RULES);
        if ($params['not'] ?? false) {
            $params['pattern'] = self::REVERSE_PATTERNS[$params['pattern']];
        }
        if (isset($params['type']) && $params['type'] == "DATE") {
            $queryBuilder->whereDate($field, $params['pattern'], $params['value']);
        } else {
            $queryBuilder->where($field, $params['pattern'], $params['pattern']);
        }
    }
}