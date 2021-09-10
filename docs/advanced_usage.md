# Advanced Usage

Learn how to use `Larapi` in Advanced usage.

# Custom Sort

You can create custom sort in your repository like this:

```php
public function sortMyName($queryBuilder, $direction)
{
    // 
}
```

# Custom Filter

You can create custom filter in your repository like this:

```php
public function filterName($queryBuilder, $method, $operator, $value, $clauseOperator, $or)
{
    // 
}
```

# Include Soft Deleted

By laravel soft deleted models will automatically be excluded from query results. However, you may force soft deleted models to be included in a query's results by calling the `withTrashed` parameter on the url:

```console
{base_url}/users?withTrashed=1
```