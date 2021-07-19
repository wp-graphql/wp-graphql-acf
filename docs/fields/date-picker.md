# Date Picker Field

The Date Picker field is added to the WPGraphQL Schema as field with the Type `String`.

Date Picker fields can be queried and a String will be returned.

Here, we have a Date Picker field named `date_picker` on the Post Edit screen within the "ACF Docs" Field Group, and "13/03/2020" is the date set.

![Date Picker field in the Edit Post screen](../img/date-picker-field-input.png?raw=true)

This field can be queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      datePicker
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
        "datePicker": "13/03/2020"
      }
    }
  }
}
```

----

- **Previous Field:** [Color Picker](./color-picker.md)
- **Next Field:** [Date/Time Picker](./date-time-picker.md)
