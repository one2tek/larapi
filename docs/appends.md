# Appends

With Laravel you can add attributes that do not have a corresponding column in your database.

# Usage

The following example appends `isAdmin` attribute:

```url
{base_url}/users?append=isAdmin
```

# Appends multiple

You can append multiple attributes separating them with a comma:

```url
{base_url}/users?append=isAdmin,isDriver
```