# oEmbed Field

oEmbed fields are added to the WPGraphQL Schema as a field with the Type `String`.

oEmbed fields can be queried, and a String will be returned.

Here, we have a oEmbed field named `oembed` on the Post Edit screen within the "ACF Docs" Field Group.

![oEmbed field in the Edit Post screen](../img/oEmbed-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      oembed
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
        "oembed": "https://www.youtube.com/watch?v=ZEytXfaWwcc"
      }
    }
  }
}
```

----

- **Previous Field:** [Number](./number.md)
- **Next Field:** [Page Link](./page-link.md)

