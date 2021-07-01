# WYSIWYG Editor Field

WYSIWYG fields are added to the WPGraphQL Schema as a field with the Type `String`.

WYSIWYG fields can be queried and a String will be returned.

Here, we have a WYSIWYG field named `wysiwyg` on the Post Edit screen within the "ACF Docs" Field Group.

![WYSIWYG field in the Edit Post screen](../img/wysiwyg-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      wysiwyg
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
        "wysiwyg": "<p>Some content in a WYSIWYG field</p>\n"
      }
    }
  }
}
```

----

- **Previous Field:** [User](./user.md)
