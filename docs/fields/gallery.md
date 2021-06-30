# Gallery Field

Gallery fields are added to the WPGraphQL Schema as a field with the Type of `['list_of' => 'MediaItem']`.

Gallery fields can be queried and a list of MediaItem types will be returned.

Since the type is a list, we can expect an array to be returned. And since the Type within the list is `MediaItem`, we can ask for fields we want returned for each `MediaItem` in the list. In this case, let's say we want to ask for the `id` of each image and the `sourceUrl`, (size large).

Here, we have a Gallery field named `gallery` on the Post Edit screen within the "ACF Docs" Field Group.

![Gallery field in the Edit Post screen](../img/gallery-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      gallery {
        id
        sourceUrl(size: LARGE)
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
        "gallery": [
          {
            "id": "YXR0YWNobWVudDoyNTY=",
            "sourceUrl": "http://wpgraphql.local/wp-content/uploads/2020/02/babe-ruth.jpg"
          },
          {
            "id": "YXR0YWNobWVudDoyNTU=",
            "sourceUrl": "http://wpgraphql.local/wp-content/uploads/2020/02/babe-ruth-baseball-986x1024.jpg"
          }
        ]
      }
    }
  }
}
```

----

- **Previous Field:** [Flexible Content](./flexible-content.md)
- **Next Field:** [Google Map](./google-map.md)

