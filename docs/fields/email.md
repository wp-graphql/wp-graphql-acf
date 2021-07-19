# Email Field

Email fields are added to the WPGraphQL Schema as a field with the Type `String`.

Email fields can be queried and a String will be returned.

Here, we have an Email field named `email` on the Post Edit screen within the "ACF Docs" Field Group.

![Email field in the Edit Post screen](../img/email-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      email
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
         "email": "test@example.com"
       }
    }
  }
}
```

----

- **Previous Field:** [Date/Time Picker](./date-time-picker.md)
- **Next Field:** [File](./file.md)
