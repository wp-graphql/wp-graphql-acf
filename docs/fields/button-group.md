# Button Group Field

Button Group fields are added to the WPGraphQL Schema as a field with the Type `String`.

Button Group fields can be queried and a String will be returned.

Here, we have a Button Group field named `button_group` on the Post Edit screen within the "ACF Docs" Field Group, and "Choice 2" is selected.

![Button Group field in the Edit Post screen](../img/button-group-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      buttonGroup
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
        "buttonGroup": "choice_2"
      }
    }
  }
}
```

----

- **Previous Field:** [Accordion](./accordion.md)
- **Next Field:** [Checkbox](./checkbox.md)

