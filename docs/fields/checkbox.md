# Checkbox Field

Checkbox fields are added to the WPGraphQL Schema as a field with the Type `[ 'list_of' => 'String' ]`.

Checkbox fields can be queried and a list (array) of Strings (the selected values) will be returned.

Here, we have a Checkbox field named `checkbox` on the Post Edit screen within the "ACF Docs" Field Group, and "Choice 1" is selected.

![Checkbox field in the Edit Post screen](../img/checkbox-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      checkbox
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
        "checkbox": [
          "choice_1"
        ]
      }
    }
  }
}
```

----

- **Previous Field:** [Button Group](./button-group.md)
- **Next Field:** [Clone](./clone.md)

