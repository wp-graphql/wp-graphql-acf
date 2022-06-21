# Password Field

Password fields are added to the WPGraphQL Schema as a field with the Type `String`.

Password fields can be queried, and a String will be returned.

Here, we have a Password field named `password` on the Post Edit screen within the "ACF Docs" Field Group.

![Password field in the Edit Post screen](../img/password-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      password
    }
  }
}
```

and the results of the query would be:

```json
{
  "data": {
    "post": {
       "acfDocs": {
         "password": "123456"
       }
    }
  }
}
```

----

- **Previous Field:** [Page Link](./page-link.md)
- **Next Field:** [Post Object](./post-object.md)
