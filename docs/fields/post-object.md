# Post Object Field

Post Object fields are added to the WPGraphQL Schema as a field with a [Union](https://graphql.org/learn/schema/#union-types) of Possible Types the field is configured to allow.

If the field is configured to allow multiple selections, it will be added to the Schema as a List Of the Union Type.

Since Post Object fields can be configured to be limited to certain Post Types, the Union will represent those Types.

For example, if the Post Object field is configured to allow Posts of the `post` and `page` types to be selected:

![Post Object field Post Type Config](../img/post-object-field-post-type-config.png?raw=true)

Then the Union type for the field will allow `Post` and `Page` types to be returned, as seen in the Schema via GraphiQL:

![Post Object field Union Possible Types](../img/post-object-field-possible-types.png?raw=true)

Here, we have a Post Object field named `post_object` on the Post Edit screen within the "ACF Docs" Field Group, configured with the Post "Hello World!".

![Post Object field in the Edit Post screen](../img/post-object-field-input-post.png?raw=true)

As a GraphQL consumer, we don't know in advance if the value is going to be a Page or a Post.

So we can specify, via GraphQL fragment, what fields we want if the object is a Post, or if it is a Page.

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      postObject {
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
        "postObject": {
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

If the input of the field was saved as a Page, instead of a Post, like so:

![Post Object field in the Edit Post screen](../img/post-object-field-input-page.png?raw=true)

Then the same query above, would return the following results:

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "postObject": {
          "__typename": "Page",
          "id": "cGFnZToy",
          "title": "Sample Page"
        }
      }
    }
  }
}
```

Now, if the field were configured to allow multiple values, the field would be added to the Schema as a `listOf`, returning an Array of the Union.

If the field were set with a value of one Page, and one Post, like so:

![Post Object field in the Edit Post screen](../img/post-object-field-input-multi.png?raw=true)

Then the results of the same query as above would be:

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "postObject": [
          {
            "__typename": "Page",
            "id": "cGFnZToy",
            "title": "Sample Page"
          },
          {
            "__typename": "Post",
            "id": "cG9zdDox",
            "title": "Hello world!",
            "date": "2020-02-20T23:12:21"
          }
        ]
      }
    }
  }
}
```

----

- **Previous Field:** [Password](./password.md)
- **Next Field:** [Radio](./radio.md)

