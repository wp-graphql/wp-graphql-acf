# Text Field

Text fields are added to the WPGraphQL Schema as a field with the Type `String`.

Text fields can be queried and a String will be returned.

Here, we have a Text field named `text` on the Post Edit screen within the "ACF Docs" Field Group.

![Text field in the Edit Post screen](../img/text-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      text
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
         "text": "Text Value"
       }
    }
  }
}
```

----

- **Previous Field:** [Taxonomy](./taxonomy.md)
- **Next Field:** [Text Area](./text-area.md)

