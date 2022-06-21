# True/False Field

True/False fields are added to the WPGraphQL Schema as a field with the Type `Boolean`.

True/False fields can be queried and a Boolean will be returned.

Here, we have a True/False field named `true_false` on the Post Edit screen within the "ACF Docs" Field Group, and "true" is selected.

![True/False field in the Edit Post screen](../img/true-false-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      trueFalse
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
        "trueFalse": true
      }
    }
  }
}
```

----

- **Previous Field:** [Time Picker](./time-picker.md)
- **Next Field:** [url](./url.md)

