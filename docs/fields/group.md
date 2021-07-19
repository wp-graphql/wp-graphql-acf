# Group Field

Group Fields are added to the WPGraphQL Schema as fields resolving to an Object Type named after the Group.

Here, we have a Group field named `group` on the Post Edit screen within the "ACF Docs" Field Group. Within the "group" field, we have a Text Field named `text_field_in_group` and a Text Area field named `text_area_field_in_group`

![Group field in the Edit Post screen](../img/group-field-input.png?raw=true)

We can query the fields within the group like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      group {
        textFieldInGroup
        textAreaFieldInGroup
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
        "group": {
          "textFieldInGroup": "Text value, in group",
          "textAreaFieldInGroup": "Text are value, in group"
        }
      }
    }
  }
}
```

----

- **Previous Field:** [Google Map](./google-map.md)
- **Next Field:** [image](./image.md)
