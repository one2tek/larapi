# Filtering

Data filtering is very easy with at `Larapi` see the examples below.

By default, all filters have to be explicitly allowed using `$whiteListFilter` property in specified Model. 

List of all valid syntax for $whiteListFilter:

```php
public static $whiteListFilter = ['*'];
public static $whiteListFilter = ['id', 'title', 'author'];
public static $whiteListFilter = ['id', 'title', 'author.*'];  
```

If the filter is ['*'] then all properties and sub-properties can be used for filtering.
If the filter is a list of model properties then only the selected properties can be filtered.
If some of the filter are a relationship then only the $whiteListFilter properties of the sub-property's model can be filtered.
If some of the filter contains a .* the all sub-properties of the relationship model can be filtered.

For more advanced use cases, [custom filter](advanced_usage?id=custom-filter) can be used.

#### Operators

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

#### Build filter

The way a filter should be formed is:

```console
{base_url}/users?filter[columnName][operator][not]=value
```

Another available parameter is `filterByOr`, `search` and `searchByOr`. 

* **columnName** -  (Required) - Name of column you want to filter, for relationships use `dots`.
* **operator** - (Optional | Default: `eq`) Type of operator you want to use.
* **not** - (Optional | Default: `false`) Negate the filter (Accepted values: not|yes|true|1).

#### Example filters

Filter all users whose id start with `1000`.

```console
{base_url}/users?filter[name][sw]=1000
```

Filter all books whose author is `Gentrit`.

```console
{base_url}/users?filter[author.name]=Gentrit
```

Filter all users whose name start with `Gentrit` or ends with `Abazi`.

```console
{base_url}/users?filterByOr[name][sw]=Gentrit&filterByOr[name][ew]=Abazi
```

[See other ways for filtering](filters_old.md)