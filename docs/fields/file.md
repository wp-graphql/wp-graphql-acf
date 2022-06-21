# File Field

File fields are added to the WPGraphQL Schema as a field with the Type `MediaItem`.

File fields can be queried and a MediaItem will be returned.

The `MediaItem` type is an Object type that has it's own fields that can be selected. So, instead of _just_ getting the File ID returned and having to ask for the MediaItem object in a follow-up request, we can ask for fields available on the MediaItem Type. For this example, we ask for the `id` and `mediaItemUrl`.

Here, we have a File field named `file` on the Post Edit screen within the "ACF Docs" Field Group.

![File field in the Edit Post screen](../img/file-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      file {
        id
        mediaItemUrl
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
        "file": {
          "id": "YXR0YWNobWVudDozMjQ=",
          "mediaItemUrl": "http://acf2.local/wp-content/uploads/2020/03/little-ceasars-receipt-01282020.pdf"
        }
      }
    }
  }
}
```

----

- **Previous Field:** [Email](./email.md)
- **Next Field:** [Flexible Content](./flexible-content.md)
