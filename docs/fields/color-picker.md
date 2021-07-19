# Color Picker Field

The Color Picker field is added to the WPGraphQL Schema as field with the Type `String`.

Color Picker fields can be queried and a String will be returned.

Here, we have a Color Picker field named `color_picker` on the Post Edit screen within the "ACF Docs" Field Group, and "#dd3333" is the value.

![Color Picker field in the Edit Post screen](../img/color-picker-field-input.png?raw=true)

This field can be queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      colorPicker
    }
  }
}
```

and the result of the query would be:

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "colorPicker": "12:30 am"
      }
    }
  }
}
```

----

- **Previous Field:** [Clone](./clone.md)
- **Next Field:** [Date Picker](./date-picker.md)
