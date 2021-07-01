# Select Field

Select fields (when configured to _not_ allow mutliple selections) are added to the WPGraphQL Schema as a field with the Type `String`.

Select fields, without multiple selections allowed, can be queried and a String will be returned.

Here, we have a Select field named `select` on the Post Edit screen within the "ACF Docs" Field Group, and "Choice 1" is selected.

![Select field in the Edit Post screen](../img/select-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      select
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
        "select": "choice_1"
      }
    }
  }
}
```

----

- **Previous Field:** [Repeater](./repeater.md)
- **Next Field:** [Tab](./tab.md)
