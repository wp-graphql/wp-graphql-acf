# Image Field

Image fields are added to the WPGraphQL Schema as a field with the Type `MediaItem`.

Image fields can be queried and a MediaItem will be returned.

The `MediaItem` type is an Object type that has it's own fields that can be selected. So, instead of _just_ getting the Image ID returned and having to ask for the MediaItem object in a follow-up request, we can ask for fields available on the MediaItem Type. For this example, we ask for the `id` and `sourceUrl`.

Here, we have an Image field named `image` on the Post Edit screen within the "ACF Docs" Field Group.

![Image field in the Edit Post screen](../img/image-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      image {
        id
        sourceUrl(size: MEDIUM)
      }
    }
  }
}
```

And the results of the query would be:

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "image": {
          "id": "YXR0YWNobWVudDozMjM=",
          "sourceUrl": "http://wpgraphql.local/wp-content/uploads/2020/03/babe-ruth-300x169.jpg"
        }
      }
    }
  }
}
```

----

- **Previous Field:** [Group](./group.md)
- **Next Field:** [Link](./link.md)
