# User Field

User fields are added to the WPGraphQL Schema as a field with a User type.

Here, we have a User field named `user` on the Post Edit screen within the "ACF Docs" Field Group, set with the User "jasonbahl" as the value.

![User field in the Edit Post screen](../img/user-field-input.png?raw=true)

This field can be queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      user {
        id
        username
        firstName
        lastName
      }
    }
  }
}
```

and the response would look like:

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "user": {
          "id": "dXNlcjox",
          "username": "jasonbahl",
          "firstName": "Jason",
          "lastName": "Bahl"
        }
      }
    }
  }
}
```

If the field is configured to allow multiple selections, it's added to the Schema as a List Of the User type.

Here, we have a User field named `user` on the Post Edit screen within the "ACF Docs" Field Group, set with the User "jasonbahl" and "WPGraphQL" as the value.

![User field in the Edit Post screen](../img/user-field-input-multiple.png?raw=true)

and the response to the same query would look like:

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "user": [
          {
            "id": "dXNlcjox",
            "username": "jasonbahl",
            "firstName": "Jason",
            "lastName": "Bahl"
          },
          {
            "id": "dXNlcjoy",
            "username": "WPGraphQL",
            "firstName": "WP",
            "lastName": "GraphQL"
          }
        ]
      }
    }
  }
}
```

----

- **Previous Field:** [Url](./url.md)
- **Next Field:** [WYSIWYG](./wysiwyg.md)
