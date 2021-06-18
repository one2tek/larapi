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