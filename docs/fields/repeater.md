# Repeater Field

Repeater Fields are added to the Schema as a List Of the Type of group that makes up the fields.

For example, we've created a Repeater Field that has a Text Field named `text_field_in_repeater` and an Image Field named `image_field_in_repeater`.

Here, the Repeater Field is populated with 2 rows:
- Row 1:
	- Text Field: Text Value 1
	- Image: 256
- Row 2:
	- Text Field: Text Value 2
	- Image: 255

![Repeater field in the Edit Post screen](../img/repeater-field-input.png?raw=true)

This field can be queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      repeater {
        textFieldInRepeater
        imageFieldInRepeater {
          databaseId
          id
          sourceUrl
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
        "repeater": [
          {
            "textFieldInRepeater": "Text Value 1",
            "imageFieldInRepeater": {
              "id": "YXR0YWNobWVudDoyNTY=",
              "sourceUrl": "http://acf2.local/wp-content/uploads/2020/02/babe-ruth.jpg"
            }
          },
          {
            "textFieldInRepeater": "Text Value 2",
            "imageFieldInRepeater": {
              "id": "YXR0YWNobWVudDoyNTU=",
              "sourceUrl": "http://acf2.local/wp-content/uploads/2020/02/babe-ruth-baseball-scaled.jpg"
            }
          }
        ]
      }
    }
  }
}
```

----

- **Previous Field:** [Relationship](./relationship.md)
- **Next Field:** [Select](./select.md)
