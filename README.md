# Query Builder Filters
Use query parameters to filter listings

## Dependencies

* PHP >= 7.2.0
* Lumen >= 7.0

## Installation via Composer
##### Add the following line to your `composer.json` and run install/update:
```bash
$ cd lumen-app
$ composer require osi-open-source/lumen-query-builder-filters
```

##### Or if you prefer, edit `composer.json` manually:
```json
{
    "require": {
        "osi-open-source/lumen-query-builder-filters": "^1.0"
    }
}
```

##### Modify the bootstrap file (```bootstrap/app.php```)
```php
use Osi\QueryBuider\SearchRequest;

$app->middleware([
    SearchRequest::class
]);
```

## Usage
With Filters
    
    /users?name=John&with=roles&take=3&skip=15

With JSON Filters

    /users?filters={"name":{"value":"John","pattern":"LIKE","not":false}}&skip=2&take=15


In your service

```php
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Osi\QueryBuilder\Filter;

    /** @var Filter */
    protected $filter;

    /**
     * Base constructor.
     * @param Filter $filter
     */
    public function __construct(Filter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->searchQuery($filters)->paginate($filters['perPage'] ?? null);
    }
        
    /**
     * @param array $filters
     * @param Builder|null $query
     * @param Closure|null $middleClosure
     * @return Builder
     */
    public function searchQuery(array $filters = [], ?Builder $query = null, ?Closure $middleClosure = null): Builder
    {
        $queryBuilder = $query ?? self::newModelInstance(User::class)->newQuery();
        if ($queryBuilder instanceof Model) {
            $queryBuilder = $queryBuilder->newQuery();
        }
        return $this->filter->search($filters, $queryBuilder, $middleClosure);
    }
```
       