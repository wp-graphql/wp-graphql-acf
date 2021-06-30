# Date/Time Picker Field

The Date/Time Picker field is added to the WPGraphQL Schema as field with the Type `String`.

Date/Time Picker fields can be queried, and a String will be returned.

Here, we have a Date/Time Picker field named `date_time_picker` on the Post Edit screen within the "ACF Docs" Field Group, and "20/03/2020 8:15 am" is the value.

![Date Picker field in the Edit Post screen](../img/date-time-picker-field-input.png?raw=true)

This field can be queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      dateTimePicker
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
        "dateTimePicker": "20/03/2020 8:15 am"
      }
    }
  }
}
```

----

- **Previous Field:** [Date Picker](./date-picker.md)
- **Next Field:** [Email](./email.md)
