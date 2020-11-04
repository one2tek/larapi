# Sorting

The `sort` query parameter is used to determine by which property the results collection will be ordered. 

# Usage
Should be defined as an array of sorting rules. They will be applied in the
order of which they are defined.

**Sorting rules**

Property | Value type | Description
-------- | ---------- | -----------
key | string | The property of the model to sort by.
direction | ASC or DESC | Which direction to sort the property by.

**Example**

```url
{base_url}/books?sort[0][key]=id&sort[0][direction]=DESC
```

Will result in the books being sorted by ```id``` in ```descending``` order.