# Including relationships

The `include` query parameter will load any Eloquent relation on the resulting models.

# Basic usage

The following query parameter will include the `logs` relation:

```url
{base_url}/users?include=logs
```

Users will have all their their `logs` related models loaded.

# Load multiple

You can load multiple relationships by separating them with a semicolon:

```url
{base_url}/users?include=logs;tasks
```

# Load nested

You can load nested relationships using the dot `.` notation:

```url
{base_url}/users?include=logs.causer
```

# Counting related relations

If you want to count the number of results from a relationship without actually loading them you may use the `withCount` query parameter, which will place a {relation}_count column on your resulting models.

```url
{base_url}/users?withCount=comments
```

# Querying Relationship Existence

Imagine you want to retrieve all blog posts that have at least one comment.
You can do this by passing `has` paramter in query:

```url
{base_url}/posts?has[]=comments
```

Nested `has` statements may also be constructed using "dot" notation. For example, you may retrieve all posts that have at least one `comment` and `vote`:

```url
{base_url}/posts?has[]=comments.votes
```

# Querying Relationship Absence

When accessing the records for a model, you may wish to limit your results based on the absence of a relationship. For example, imagine you want to retrieve all blog posts that don't have any `comments`. To do so, you may pass `doesntHave` paramter in query:

```url
{base_url}/posts?doesntHave[]=comments
```