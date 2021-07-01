# Range Field

Range fields are added to the WPGraphQL Schema as a field with the Type `Float`.

Range fields can be queried, and a Float will be returned.

Here, we have a Range field named `range` on the Post Edit screen within the "ACF Docs" Field Group.

![Range field in the Edit Post screen](../img/range-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      range
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
         "range": 5
       }
    }
  }
}
```

----

- **Previous Field:** [Radio](./radio.md)
- **Next Field:** [Relationship](./relationship.md)
