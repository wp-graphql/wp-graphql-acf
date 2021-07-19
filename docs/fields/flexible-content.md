# Flexible Content Field

The Flexible Content is a powerful ACF field that allows for groups of fields to be organized into "Layouts".

These Layouts can be made up of other types of fields, and can be added and arranged in any order.

Flexible Content Fields are added to the WPGraphQL Schema as a List Of [Unions](https://graphql.org/learn/schema/#union-types).

The Union for a Flex Field is made up of each Layout in the Flex Field as the possible Types.

In our example, we've created a Flex Field with 3 layouts named "Layout One", "Layout Two" and "Layout Three". In the Schema, we can see the Flex Field Union's Possible Types are these 3 layouts.

![Flex Fields Schema Union Possible Types](../img/flex-field-union-possible-types.png?raw=true)

Each of these Layout types will contain the fields defined for the layout and can be queried like fields in any other Group.

Here's an example of a Flex Field named `flexible_content`, with 3 layouts:

- Layout One
	- Text field named "text"
	- Text field named "another_text_field"
- Layout Two
	- Image field named "image"
- Layout Three
	- Gallery field named "gallery"

Above are the possible layouts and their fields. These layouts can be added and arranged in any order. While we, as a GraphQL consumer, don't know ahead of time what order they will be in, we _do_ know what the possibilities are.

Here's an example of a Flex Field named `flexible_content` with the values saved as "Layout One", "Layout Two" and "Layout Three", in that order, all populated with their respective fields.

![Flex field in the Edit Post screen](../img/flex-field-input.png?raw=true)

We can query this field like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      flexibleContent {
        __typename
        ... on Post_Acfdocs_FlexibleContent_LayoutOne {
          text
          anotherTextField
        }
        ... on Post_Acfdocs_FlexibleContent_LayoutTwo {
          image {
            id
            sourceUrl(size: MEDIUM)
          }
        }
        ... on Post_Acfdocs_FlexibleContent_LayoutThree {
          gallery {
            id
            sourceUrl(size: MEDIUM)
          }
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
        "flexibleContent": [
          {
            "__typename": "Post_Acfdocs_FlexibleContent_LayoutOne",
            "text": "Text Value One",
            "anotherTextField": "Another Text Value"
          },
          {
            "__typename": "Post_Acfdocs_FlexibleContent_LayoutTwo",
            "image": {
              "id": "YXR0YWNobWVudDoyNTY=",
              "sourceUrl": "http://acf2.local/wp-content/uploads/2020/02/babe-ruth-300x169.jpg"
            }
          },
          {
            "__typename": "Post_Acfdocs_FlexibleContent_LayoutThree",
            "gallery": [
              {
                "id": "YXR0YWNobWVudDoyNTY=",
                "sourceUrl": "http://acf2.local/wp-content/uploads/2020/02/babe-ruth-300x169.jpg"
              },
              {
                "id": "YXR0YWNobWVudDoyNTU=",
                "sourceUrl": "http://acf2.local/wp-content/uploads/2020/02/babe-ruth-baseball-289x300.jpg"
              }
            ]
          }
        ]
      }
    }
  }
}
```

If we were to re-arrange the layouts, so that the order was "Layout Three", "Layout One", "Layout Two", the results of the query would be:

```json
"data": {
    "post": {
      "acfDocs": {
        "flexibleContent": [
          {
            "__typename": "Post_Acfdocs_FlexibleContent_LayoutThree",
            "gallery": [
              {
                "id": "YXR0YWNobWVudDoyNTY=",
                "sourceUrl": "http://acf2.local/wp-content/uploads/2020/02/babe-ruth-300x169.jpg"
              },
              {
                "id": "YXR0YWNobWVudDoyNTU=",
                "sourceUrl": "http://acf2.local/wp-content/uploads/2020/02/babe-ruth-baseball-289x300.jpg"
              }
            ]
          }
          {
            "__typename": "Post_Acfdocs_FlexibleContent_LayoutOne",
            "text": "Text Value One",
            "anotherTextField": "Another Text Value"
          },
          {
            "__typename": "Post_Acfdocs_FlexibleContent_LayoutTwo",
            "image": {
              "id": "YXR0YWNobWVudDoyNTY=",
              "sourceUrl": "http://acf2.local/wp-content/uploads/2020/02/babe-ruth-300x169.jpg"
            }
          },
        ]
      }
    }
  }
```

----

- **Previous Field:** [File](./file.md)
- **Next Field:** [Gallery](./gallery.md)
