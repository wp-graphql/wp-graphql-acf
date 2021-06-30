# Relationship Field

Relationship fields are added to the WPGraphQL Schema as a field with a [Union](https://graphql.org/learn/schema/#union-types) of Possible Types the field is configured to allow.

Since Relationship fields can be configured to be limited to certain Post Types, the Union will represent those Types.

For example, if the Post Object field is configured to allow Posts of the `post` and `page` types to be selected:

![Relationship field Post Type Config](../img/relationship-field-post-type-config.png?raw=true)

Then the Union type for the field will allow `Post` and `Page` types to be returned, as seen in the Schema via GraphiQL:

![Relationship field Union Possible Types](../img/relationship-field-possible-types.png?raw=true)

Here, we have a Relationship field named `relationship` on the Post Edit screen within the "ACF Docs" Field Group, and the value is set to "Hello World!" post, and the "Sample Page" page.

![Relationship field in the Edit Post screen](../img/relationship-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      relationship {
        __typename
        ... on Post {
          id
          title
          date
        }
        ... on Page {
          id
          title
        }
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
        "relationship": [
          {
            "__typename": "Post",
            "id": "cG9zdDox",
            "title": "Hello world!",
            "date": "2020-02-20T23:12:21"
          },
          {
            "__typename": "Page",
            "id": "cGFnZToy",
            "title": "Sample Page"
          }
        ]
      }
    }
  }
}
```

----

- **Previous Field:** [Range](./range.md)
- **Next Field:** [Repeater](./repeater.md)

