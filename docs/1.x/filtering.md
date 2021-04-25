# Filtering

Data filtering is very easy with at `Larapi` see the examples below.

By default, all filters have to be explicitly allowed using `$whiteListFilter` property in specified Model. 

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
* **not** - (Optional | Default: `false`) Negate the filter (Accepted values: yes|true|1).

#### Example filters

Filter all users whose id start with `1000`.

```console
{base_url}/users?filter[name][sw]=1000
```

Filter all books whose author is `Gentrit`.

```console
{base_url}/users?filter[name]=author.name
```

Filter all users whose name start with `Gentrit` or ends with `Abazi`.

```console
{base_url}/users?filterByOr[name][sw]=Gentrit&filterByOr[name][ew]=Abazi
```

[See other ways for filtering](filters_old.md)