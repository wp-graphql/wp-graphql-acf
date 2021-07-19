# Radio Button Field

Radio Button fields are added to the WPGraphQL Schema as a field with the Type `String`.

Radio Button fields can be queried and a String will be returned.

Here, we have a Radio Button field named `radio_button` on the Post Edit screen within the "ACF Docs" Field Group, and "Choice 2" is selected.

![Radio Button field in the Edit Post screen](../img/radio-button-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      radioButton
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
        "radioButton": "choice_2"
      }
    }
  }
}
```

----

- **Previous Field:** [Post Object](./post-object.md)
- **Next Field:** [Range](./range.md)
