# Repository

`Larapi` included Repository class for requesting entities from your database.

# Create repository

To create repository is easy you need just to define own model:

```php
<?php
class UserRepository extends Repository
{
    public function getModel()
    {
        return new User();
    }
}
```

# Default sort

If you want to sort default data after get from Repository you can do like this:

```php
<?php
class UserRepository extends Repository
{
    protected $sortProperty = 'id';

    // 0 = ASC, 1 = DESC
    protected $sortDirection = 1;

    public function getModel()
    {
        return new User();
    }
}
```

# Functions

The examples will use a hypothetical Eloquent model named `User`.

### get (array $options = [])

Get all `User` rows

### getWithCount (array $options = [])
Get all `User` rows with Count

### getById ($id, array $options = [])

Get one `User` by primary key

### getRecent (array $options = [])

Get `User` rows ordered by `created_at` descending

### getRecentWhere (string $column, mixed $value, array $options = [])

Get `User` rows where `$column=$value`, ordered by `created_at` descending

### getWhere (string $column, mixed $value, array $options = [])

Get `User` rows where `$column=$value`

### getWhereArray (array $clauses, array $options = [])

Get `User` rows by multiple where clauses (`[$column1 => $value1, $column2 => $value2]`)

### getWhereIn (string $column, array $values, array $options = [])

Get `User` rows where `$column` can be any of the values given by `$values`

### getLatest (array $options = [])

Get the most recent `User`

### getLatestWhere (string $column, mixed $value, array $options = [])

Get the most recent `User` where `$column=$value`

### delete ($id)

Delete `User` rows by primary key

### deleteWhere ($column, $value)

Delete `User` rows where `$column=$value`

### deleteWhereArray (array $clauses)

Delete `User` rows by multiple where clauses (`[$column1 => $value1, $column2 => $value2]`)