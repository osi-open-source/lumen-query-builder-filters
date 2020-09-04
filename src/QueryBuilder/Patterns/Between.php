<?php

namespace QueryBuilder\Patterns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

/**
 * Class Between
 * @package QueryBuilder\Patterns
 */
class Between implements Pattern
{
    const RULES = [
        'type'       => 'in:date',
        'not'        => 'boolean',
        'startRange' => 'string',
        'endRange'   => 'required_without:startRange'
    ];

    /**
     * @param string $field
     * @param array $params
     * @param Builder $queryBuilder
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function apply(string $field, array $params, Builder $queryBuilder): void
    {
        Validator::validate($params, self::RULES);

        if (isset($params['type']) && $params['type'] == "DATE") {
            $params['startRange'] = new Carbon($params['startRange'] ?? '0001-01-01');
            $params['endRange'] = new Carbon($params['endRange'] ?? '9999-12-01');
        }
        $queryBuilder->whereBetween($field, [$params['startRange'] ?? 0, $params['endRange'] ?? 9999999999]);
    }
}