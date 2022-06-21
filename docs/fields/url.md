# URL Field

Url fields are added to the WPGraphQL Schema as a field with the Type `String`.

Url fields can be queried and a String will be returned.

Here, we have a URL field named `url` on the Post Edit screen within the "ACF Docs" Field Group.

![Url field in the Edit Post screen](../img/url-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      url
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
         "url": "https://wpgraphql.com"
       }
    }
  }
}
```

----

- **Previous Field:** [True/False](./true-false.md)
- **Next Field:** [User](./user.md)

