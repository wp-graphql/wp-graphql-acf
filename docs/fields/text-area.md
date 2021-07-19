# Text Area Field

Text Area fields are added to the WPGraphQL Schema as a field with the Type `String`.

Text Area fields can be queried and a String will be returned.

Here, we have a Text Area field named `text_area` on the Post Edit screen within the "ACF Docs" Field Group.

![Text Area field in the Edit Post screen](../img/text-area-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      textArea
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
         "textArea": "Text value"
       }
    }
  }
}
```

----

- **Previous Field:** [Text](./text.md)
- **Next Field:** [Time Picker](./time-picker.md)

