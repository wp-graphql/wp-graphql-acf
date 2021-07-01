# Time Picker Field

The Time Picker field is added to the WPGraphQL Schema as field with the Type `String`.

Time Picker fields can be queried and a String will be returned.

Here, we have a Time Picker field named `time_picker` on the Post Edit screen within the "ACF Docs" Field Group, and "12:30 am" is the value.

![Time Picker field in the Edit Post screen](../img/time-picker-field-input.png?raw=true)

This field can be queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      timePicker
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
        "timePicker": "12:30 am"
      }
    }
  }
}
```

----

- **Previous Field:** [Text Area](text-area.md)
- **Next Field:** [True/False](true-false.md)

