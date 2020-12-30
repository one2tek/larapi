# Sorting

The `sortByAsc` and `sortByDesc` query parameters are used to determine by which property the results collection will be ordered. 

For more advanced use cases, [custom sorts](advanced_usage?id=custom-sort) can be used.

# Usage

The following query parameter `sortByAsc` will sort results by  from the lowest value to the highest value:

```url
{base_url}/books?sortByAsc=id
```

The following query parameter `sortByDesc` will sort results by  from the highest value to the lowest value:

```url
{base_url}/books?sortByDesc=id
```

# Sort multiple columns

You can sort multiple columns separating them with a comma:

```url
{base_url}/books?sortByDesc=id,name
```