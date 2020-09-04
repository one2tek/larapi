### Introduction

### [Parent repository](https://github.com/esbenp/larapi)

We have updated to Laravel 7 with some extra features.

Larapi Components comes included with...
* A modern exception handler for APIs.
* A Controller class that gives sorting, filtering, eager loading and pagination for your endpoints.
* A Repository class for requesting entities from your database.

### Global scopes

**Without global scopes**

`/books?exludeGlobalScopes[]=not_delivered`

If we have a global scope named "not_delivered" this call with remove the global scope

### Create Controller, Service, Repository & Model with one Command.
```php artisan component:make {parent} {name}```

### Eager loading

**Simple eager load**

`/books?includes[]=author`

Will return a collection of 5 `Book`s eager loaded with `Author`.

**IDs mode**

`/books?includes[]=author-ids`

Will return a collection of `Book`s eager loaded with the ID of their `Author`

**Sideload mode**

`/books?includes[]=author-sideload`

Will return a collection of `Book`s and a eager loaded collection of their
`Author`s in the root scope.

### Pagination

Two parameters are available: `limit` and `page`. `limit` will determine the number of
records per page and `page` will determine the current page.

`/books?limit=10&page=3`

Will return books number 30-40.

### Sorting

Should be defined as an array of sorting rules. They will be applied in the
order of which they are defined.

**Sorting rules**

Property | Value type | Description
-------- | ---------- | -----------
key | string | The property of the model to sort by
direction | ASC or DESC | Which direction to sort the property by

**Example**

```json
{
	"sort": [
		{
			"key": "id",
			"direction": "DESC"
		}
	]
}
```

Will result in the books being sorted by ```id``` in ```descending``` order.

### Filtering

Before use filters make sure you have declared $whiteListFilter in specified Model.
Example:
```
/**
 * Columns that can be filtered.
 *
 * @var array
 */
public static $whiteListFilter = ['first_name', 'posts.body'];
```

Filters should be defined as an array of filter groups.

**Filter groups**

Property | Value type | Description
-------- | ---------- | -----------
or | boolean | Should the filters in this group be grouped by logical OR or AND operator
filters | array | Array of filters (see syntax below)

**Filters**

Property | Value type | Description
-------- | ---------- | -----------
column | string | The property of the model to filter by (can also be custom filter)
value | mixed | The value to search for
operator | string | The filter operator to use (see different types below)
not | boolean | Negate the filter

**Operators**

Type | Description
---- | -----------
ct | String contains
sw | Starts with
ew | Ends with
eq | Equals
gt | Greater than
gte| Greater than or equalTo
lte | Lesser than or equalTo
lt | Lesser than
in | In array
bt | Between

#### Example filter

Below you can find how to filter all users that have first name "Gentrit".

```json
{
	"filter_groups": [
		{
			"filters": [
				{
					"column": "first_name",
					"operator": "eq",
					"value": "Gentrit"
				}
			]
		}
	]
}
```

You can create Custom filter in Repostory like this:

```php
public function filterAuthorName($queryBuilder, $method, $operator, $value)
{
     switch ($method) {
        case 'where':
            $queryBuilder->whereHas('author', function ($q) use ($operator, $value) {
                $q->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), $operator, $value);
            });
            break;
        
        case 'orWhere':
            $queryBuilder->orWhereHas('author', function ($q) use ($operator, $value) {
                $q->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), $operator, $value);
            });
            break;
}
```

**Custom filter function**

Argument | Description
-------- | -----------
$queryBuilder | The Eloquent query builder.
$method | The where method to use (`where`, `orWhere`, `whereIn`, `orWhereIn` etc.).
$operator | Can operator to use for non-in wheres (`!=`, `=`, `>` etc.).
$value | The filter value.

## Repository functions

The examples will use a hypothetical Eloquent model named `User`.

### get (array $options = [])

Get all `User` rows

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
