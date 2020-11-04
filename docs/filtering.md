# Filtering

Data filtering is very easy with at `Larapi` see the examples below.

# Usage

By default, all filters have to be explicitly allowed using `$whiteListFilter` property in specified Model. 

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

Filter all books whose author is Gentrit.
```json
{
	"filter_groups": [
		{
			"filters": [
				{
					"column": "author.name",
					"operator": "eq",
					"value": "Gentrit"
				}
			]
		}
    ]
}
```