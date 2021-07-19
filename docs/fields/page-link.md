# Page Link Field

Page Link fields are added to the WPGraphQL Schema as a field with a [Union](https://graphql.org/learn/schema/#union-types) of Possible Types the field is configured to allow.

Since Page Link fields can be configured to be limited to certain Post Types, the Union will represent those Types.

For example, if the Post Object field is configured to allow Posts of the `post` and `page` types to be selected:

![Page Link field Post Type Config](../img/page-link-field-post-type-config.png?raw=true)

Then the Union type for the field will allow `Post` and `Page` types to be returned, as seen in the Schema via GraphiQL:

![Page Link field Union Possible Types](../img/page-link-field-possible-types.png?raw=true)

Here, we have a Page Link field named `page_link` on the Post Edit screen within the "ACF Docs" Field Group, and the value is set to the "Sample Page" page.

![Page Link field in the Edit Post screen](../img/page-link-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      pageLink {
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
        "pageLink": {
          "__typename": "Page",
          "id": "cGFnZToy",
          "title": "Sample Page"
        }
      }
    }
  }
}
```

Here, we set the value to the "Hello World" Post:

![Page Link field in the Edit Post screen](../img/page-link-field-input-2.png?raw=true)

And the results of the same query are now:

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "pageLink": {
          "__typename": "Post",
          "id": "cG9zdDox",
          "title": "Hello world!",
          "date": "2020-02-20T23:12:21"
        }
      }
    }
  }
}
```

----

- **Previous Field:** [Oembed](./oembed.md)
- **Next Field:** [Checkbox](./password.md)

