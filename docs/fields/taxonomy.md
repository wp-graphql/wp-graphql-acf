# Taxonomy Field

The Taxonomy field is added to the GraphQL Schema as a List Of the Taxonomy Type.

For example, if the field is configured to the "Category" taxonomy, then the field in the Schema will be a List of the Category type.

![Taxonomy field Taxonomy Config](../img/taxonomy-field-taxonomy-config.png?raw=true)

Here, we have a Taxonomy field named `taxonomy` on the Post Edit screen within the "ACF Docs" Field Group, configured with the Category "Test Category".

![Taxonomy field in the Edit Post screen](../img/taxonomy-field-input.png?raw=true)

This field can be queried like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      taxonomy {
        __typename
        id
        name
      }
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
        "taxonomy": [
          {
            "__typename": "Category",
            "id": "Y2F0ZWdvcnk6Mg==",
            "name": "Test Category"
          }
        ]
      }
    }
  }
}
```

----

- **Previous Field:** [Tab](./tab.md)
- **Next Field:** [Text](./text.md)

