# Number Field

Number fields are added to the WPGraphQL Schema as a field with the Type `Float`.

Number fields can be queried, and a Float will be returned.

Here, we have a Number field named `number` on the Post Edit screen within the "ACF Docs" Field Group.

![Number field in the Edit Post screen](../img/number-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      number
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
         "number": 5
       }
    }
  }
}
```

----

- **Previous Field:** [Message](./message.md)
- **Next Field:** [Oembed](./oembed.md)
