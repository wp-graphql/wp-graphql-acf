# Link Field

Link fields are added to the WPGraphQL Schema as a field with the Type `ACF_Link`.

Link fields can be queried and a `ACF_Link` will be returned. The ACF Link is an object with fields that can be selected.

The available fields on the `ACF_Link` Type are:

- **target** (String): The target of the link
- **title** (String): The target of the link
- **url** (String): The url of the link

Here, we have a Link field named `link` on the Post Edit screen within the "ACF Docs" Field Group.

![Link field in the Edit Post screen](../img/link-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      link {
        target
        title
        url
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
        "link": {
          "target": "",
          "title": "Hello world!",
          "url": "http://acf2.local/hello-world/"
        }
      }
    }
  }
}
```

----

- **Previous Field:** [Image](./image.md)
- **Next Field:** [Message](./message.md)

