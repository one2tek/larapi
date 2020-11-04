# Selecting fields

Sometimes you'll want to fetch only a couple fields to reduce the overall size of your SQL query. This can be done by specifying some fields request query parameter.

# Basic usage

The following example fetches only the users' id and name:

```markdown
{base_url}/users?select=id,name
```

The SQL query will look like this:

```sql
`SELECT "id", "name" FROM "users"`
```

# Selecting fields for included relations

The following example fetches only the authors' id and name:

```markdown
{base_url}/books?include=author:id,name.
```