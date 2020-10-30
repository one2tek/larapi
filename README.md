### Introduction

**Larapi** offers you to do modern API development in Laravel. Package is inspired from [esbenp/larapi](https://github.com/esbenp/larapi) with some cool modifications and also support for new versions of Laravel.

**Larapi** comes included with...
* A modern exception handler for APIs.
* A Controller class that gives response, parse data for your endpoints.
* A Repository class for requesting entities from your database.
* Sorting.
* Filtering.
* Eager loading.
* Pagination.
* Selecting columns dynamically.
* Selecting scopes dynamically.
* Slack formatter.
* A class for making internal API requests.

### Installation
1. Go to your **Controller.php** and extends **LaravelController**.
![Controller.php](https://i.imgur.com/gjsq1Kz.png)
2. Create a Base **Repository** class like below.
![Repository.php](https://i.imgur.com/CU4cwCm.png)
3. Example **Repository** for **Users**.
![UsersRepository.php](https://i.imgur.com/iXqsVly.png)
4. Example **Controller or Service** for **Users**.
![UsersController.php](https://i.imgur.com/dSYZXyt.png)

### [Check how to use in real application](https://github.com/gentritabazi01/Clean-Laravel-Api)

### Selects

**Specifying A Select Clause**

You may not always want to select all columns from a database table. Using the select method, you can specify a custom select clause for the query:

`{base_url}/users?select=id,first_name`

### Scopes

**Include Scopes**

`{base_url}/books?scope[]=delivered`

If we have a scope named "***delivered***" this call will include the scope.

**Without global scopes**

`{base_url}/books?exludeGlobalScopes[]=not_delivered`

If we have a global scope named "**not_delivered**" this call with remove the global scope.

### Create Controller, Service, Repository & Model with one Command.

If you want to create Controller, Service, Repository, Model, Exceptions and Events with one Command, then you can use:

```php artisan component:make {parent} {name}```

### Eager loading

**Simple eager load**

`{base_url}/books?includes[]=author` or `{base_url}/books?include=author`.

Will return a collection of 5 `Book`s eager loaded with `Author`.

**IDs mode**

`{base_url}/books?modeIds[]=author`

Will return a collection of `Book`s eager loaded with the ID of their `Author`

**Sideload mode**

`{base_url}/books?modeSideload[]=author`

Will return a collection of `Book`s and a eager loaded collection of their
`Author`s in the root scope.

### Querying Relationship Existence

Imagine you want to retrieve all blog posts that have at least one comment.

`{base_url}/posts?has[]=comments`

Nested **has** statements may also be constructed using **"dot"** notation. For example, you may retrieve all posts that have at least one comment and vote:

`{base_url}/posts?has[]=comments.votes`

### Querying Relationship Absence

When accessing the records for a model, you may wish to limit your results based on the absence of a relationship. For example, imagine you want to retrieve all blog posts that don't have any comments. To do so, you may pass the name of the relationship to the **doesntHave**:

`{base_url}/posts?doesntHave[]=comments`

### Advanced Eager loading

If you want to get the relationships in a more advanced way then look at the code below.

```json
{
	"with": [
		{
			"name": "relation_name",
			"select": ["column1", "column2", "column3"],
			"group_by": ["column1"],
			"sort": [
				{
					"key": "column2",
					"direction": "DESC"
				}
			]
		}
	]
}
```

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
lt | Lesser than
lte | Lesser than or equalTo
in | In array
bt | Between

#### Example filters

Filter all users whose name start with “Gentrit” or ends with “Abazi”.

```SELECT * FROM `users` WHERE name LIKE "Gentrit%" OR name LIKE "%Abazi"```

```json
{
	"filter_groups": [
		{
			"or": true,
			"filters": [
				{
					"column": "name",
					"operator": "sw",
					"value": "Gentrit"
				},
				{
					"column": "name",
					"operator": "ew",
					"value": "Abazi"
				}
			]
		}
	]
}
```

Filter all users whose name start with “A” and which were born between years 1990 and 2000.

```SELECT * FROM `users` WHERE (name LIKE "A%") AND (`birth_year` >= 1990 and `birth_year` <= 2000)```

```json
{
	"filter_groups": [
		{
			"filters": [
				{
					"column": "name",
					"operator": "sw",
					"value": "A"
				}
			]
		},
		{
			"filters": [
				{
					"column": "birth_year",
					"value": 1990,
					"operator": "gte"
				},
				{
					"column": "birth_year",
					"value": 2000,
					"operator": "lte"
				}
			]
		}
	]
}
```

You can create Custom filter in Repostory like this:

```php
public function filterAuthorName($queryBuilder, $method, $operator, $value, $clauseOperator, $or)
{
	// Query.
	$queryFunction = function ($q) use ($operator, $value, $method, $clauseOperator) {
		// Remove or from method, because we will use Subquery.
		$method = str_replace('or', '', $method);

		if ($clauseOperator == false) {
			$q->$method(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), $value);
		} else {
			$q->$method(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), $operator, $value);
		}
	};

	// Execute query.
	if ($or == true) {
		$queryBuilder->orWhereHas('author', $queryFunction);
	} else {
		$queryBuilder->whereHas('author', $queryFunction);
	};
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

## Slack

If you send errors in slack then the package provides a dynamic way to send more data in slack.

First create class like this:

```
<?php

namespace Infrastructure\Formatters;

class SlackFormatter
{
    public static function data()
    {
        $records = [];
        $records['Date & Time'] = date('Y-m-d H:i:s');

        return $records;
    }
}
```

And set the class path in config file **config/larapi-components.php** like this:
```
'slack_formatter' => '\Infrastructure\Formatters\SlackFormatter'
```
