<?php

namespace Osi\QueryBuilder\Patterns;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

/**
 * Class Scope
 * @package Osi\QueryBuilder\Patterns
 */
class Scope implements Pattern
{
    const RULES = [
        'pattern' => 'required|in:SCOPE',
        'not'     => 'boolean'
    ];

    /**
     * @param string $field
     * @param array $params
     * @param Builder $queryBuilder
     * @return void
     * @throws InvalidArgumentException
     */
    public function apply(string $field, array $params, Builder $queryBuilder): void
    {
        Validator::validate($params, self::RULES);
        if (!$queryBuilder->hasNamedScope($params['name'])) {
            throw new InvalidArgumentException("Scope is not defined!");
        }
        if ($params['not'] ?? false) {
            throw new InvalidArgumentException("Unsupported negative filter!");
        }
        $params = [];
        if(isset($params['value'])) {
            $params[] = $params['value'];
        }
        $queryBuilder->scopes([
            $params['name'] => $params
        ]);
    }
}
