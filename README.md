# WPGraphQL for Advanced Custom Fields

WPGraphQL for Advanced Custom Fields automatically exposes your ACF fields to the WPGraphQL Schema. 

- [Install and Activate](#install-and-activate)
  - [Installing from Github](#install-from-github)
  - [Installing with Composer](#install-with-composer)
- [Dependencies](#dependencies)
- [Adding Fields to the WPGraphQL Schema](#adding-fields-to-wpgraphql)
- [Supported Fields](#supported-fields)
  - [Text](#text-field)
  - [Text Area](#text-area-field)
  - [Number](#number-field)
  - [Range](#range-field)
  - [Email](#email-field)
  - [URL](#url-field)
  - [Password](#password-field)
  - [Image](#image-field)
  - [File](#file-field)
  - [WYSIWYG Editor](#wysiwyg-field)
  - [oEmbed](#oembed-field)
  - [Gallery](#gallery-field)
  - [Select](#select-field)
  - [Checkbox](#checkbox-field)
  - [Radio Button](#radio-button-field)
  - [Button Group](#button-group-field)
  - [True/False](#true-false-field)
  - [Link](#link-field)
  - [Post Object](#post-object-field)
  - [Page Link](#page-link-field)
  - [Relationship](#relationship-field)
  - [Taxonomy](#taxonomy-field)
  - [User](#user-field)
  - [Google Map](#google-map-field)
  - [Date Picker](#date-picker-field)
  - [Date/Time Picker](#date-time-picker-field)
  - [Time Picker](#time-picker-field)
  - [Color Picker](#color-picker-field)
  - [Message](#message-field)
  - [Accordion](#accordion-field)
  - [Tab](#tab-field)
  - [Group](#group-field)
  - [Repeater](#repeater-field)
  - [Flexible Content](#flexible-content-field)
  - [Clone](#clone-field)
- [Location Rules](#location-rules)

## Install and Activate <a name="install-and-activate" />
WPGraphQL for Advanced Custom Fields is not currently available on the WordPress.org repository, so you must download it from Github, or Composer. 

### Installing From Github <a name="install-from-github" />
To install the plugin from Github, you can [download the latest release zip file](https://github.com/wp-graphql/wp-graphql-acf/archive/master.zip), upload the Zip file to your WordPress install, and activate the plugin. 

[Click here](https://wordpress.org/support/article/managing-plugins/) to learn more about installing WordPress plugins from a Zip file.

### Installing from Composer <a name="install-with-composer" />

`composer require wp-graphql/wp-graphql-acf`

## Dependencies <a name="dependencies" />
In order to use WPGraphQL for Advanced Custom Fields, you must have [WPGraphQL](https://github.com/wp-graphql/wp-graphql) and [Advanced Custom Fields](https://advancedcustomfields.com) (free or pro) installed and activated. 

## Adding Fields to the WPGraphQL Schema <a name="adding-fields-to-wpgraphql" />

**TL;DR:** [Here's a video](https://www.youtube.com/watch?v=rIg4MHc8elg) showing an overview of usage.

Advanced Custom Fields, or ACF for short, enables users to add Field Groups, either using a [Graphical User Interface](https://www.advancedcustomfields.com/resources/creating-a-field-group/), [PHP code](https://www.advancedcustomfields.com/resources/register-fields-via-php/), or [local JSON](https://www.advancedcustomfields.com/resources/local-json/) to various screens in the WordPress dashboard, such as (but not limited to) the Edit Post, Edit User and Edit Term screens. 

Whatever method you use to register ACF fields to your WordPress site should work with WPGraphQL for Advanced Custom Fields. For the sake of simplicity, the documentation below will _primarily_ use the Graphic User Interface for examples. 

### Add ACF Fields to the WPGraphQL Schema

The first step in using Advanced Custom Fields with WPGraphQL is to [create an ACF Field Group](https://www.advancedcustomfields.com/resources/creating-a-field-group/). 

By default, field groups are _not_ exposed to WPGraphQL. You must opt-in to expose your ACF Field Groups and fields to the WPGraphQL Schema as some information managed by your ACF fields may not be intended for exposure in a queryable API like WPGraphQL.

#### Show in GraphQL Setting

To have your ACF Field Group show in the WPGraphQL Schema, you need to configure the Field Group to "Show in GraphQL".

##### Using the ACF GUI

When using the ACF Graphic User Interface for creating fields, WPGraphQL for Advanced Custom Fields adds a **Show in GraphQL** field to Field Groups.

Setting the value of this field to "Yes" will show the field group in the WPGraphQL Schema, if a [GraphQL Field Name](#graphql-field-name) is also set

![Show in GraphQL Setting for ACF Field Groups](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/field-group-show-in-graphql.png?raw=true)

##### Registering Fields in PHP

When registering ACF Fields in PHP, `@todo`

## Supported Fields
In order to document interacting with the fields in GraphQL, an example field group has been created with one field of each type. 

To replicate the same field group documented here you can [download the exported field group](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/download/field-group-export.zip) and [import it](https://support.advancedcustomfields.com/forums/topic/importing-exporting-acf-settings/) into your environment. 

For the sake of documentation, this example field group has the [location rule](#location-rules) set to "Post Type is equal to Post", which will allow for the fields to be entered when creating and editing Posts in the WordPress dashboard, and will expose the fields to the `Post` type in the WPGraphQL Schema.

![Location rule set to Post Type is equal to Post](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/location-rule-post-type-post.png?raw=true)

### Text Field <a name="text-field" />

Text fields are added to the WPGraphQL Schema as a field with the Type `String`.

Text fields can be queried and a String will be returned. 

Here, we have a Text field named `text` on the Post Edit screen within the "ACF Docs" Field Group.

![Text field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/text-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      textArea
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
         "textArea": "Text Area Value"
       }
    }
  }
}
```

![Text field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/text-field-query.png?raw=true)

### Text Area Field <a name="text-area-field" />

Text Area fields are added to the WPGraphQL Schema as a field with the Type `String`.

Text Area fields can be queried and a String will be returned. 

Here, we have a Text Area field named `text_area` on the Post Edit screen within the "ACF Docs" Field Group.

![Text Area field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/text-area-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      textArea
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
         "textArea": "Text value"
       }
    }
  }
}
```

![Text field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/text-field-query.png?raw=true)


### Number Field <a name="number-field" />

Number fields are added to the WPGraphQL Schema as a field with the Type `Integer`.

Number fields can be queried and an Integer will be returned. 

Here, we have a Number field named `number` on the Post Edit screen within the "ACF Docs" Field Group.

![Number field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/number-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      number
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
         "number": 5
       }
    }
  }
}
```

![Number field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/number-field-query.png?raw=true)

### Range Field <a name="range-field" />

Range fields are added to the WPGraphQL Schema as a field with the Type `Integer`.

Range fields can be queried and an Integer will be returned. 

Here, we have a Range field named `range` on the Post Edit screen within the "ACF Docs" Field Group.

![Range field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/range-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      range
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
         "range": 5
       }
    }
  }
}
```

![Range field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/range-field-query.png?raw=true)

### Email Field <a name="email-field" />

Email fields are added to the WPGraphQL Schema as a field with the Type `String`.

Email fields can be queried and a String will be returned. 

Here, we have an Email field named `email` on the Post Edit screen within the "ACF Docs" Field Group.

![Email field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/email-field-input.png?raw=true)

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

![Email field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/email-field-query.png?raw=true)

### URL Field <a name="url-field" />

Url fields are added to the WPGraphQL Schema as a field with the Type `String`.

Url fields can be queried and a String will be returned. 

Here, we have a URL field named `url` on the Post Edit screen within the "ACF Docs" Field Group.

![Url field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/url-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      url
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
         "url": "https://wpgraphql.com"
       }
    }
  }
}
```

![URL field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/url-field-query.png?raw=true)

### Password Field <a name="password-field" />

Password fields are added to the WPGraphQL Schema as a field with the Type `String`.

Password fields can be queried and a String will be returned. 

Here, we have a Password field named `password` on the Post Edit screen within the "ACF Docs" Field Group.

![Password field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/password-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    acfDocs {
      password
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
         "password": "123456"
       }
    }
  }
}
```

![Password field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/password-field-query.png?raw=true)

### Image Field <a name="image-field" />

Image fields are added to the WPGraphQL Schema as a field with the Type `MediaItem`.

Image fields can be queried and a MediaItem will be returned. 

The `MediaItem` type is an Object type that has it's own fields that can be selected. So, instead of _just_ getting the Image ID returned and having to ask for the MediaItem object in a follow-up request, we can ask for fields available on the MediaItem Type. For this example, we ask for the `id` and `sourceUrl`.

Here, we have an Image field named `image` on the Post Edit screen within the "ACF Docs" Field Group.

![Image field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/image-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post( id: "acf-example-test" idType: URI ) {
    image {
      id
      sourceUrl(size: MEDIUM)
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
        "image": {
          "id": "YXR0YWNobWVudDozMjM=",
          "sourceUrl": "http://wpgraphql.local/wp-content/uploads/2020/03/babe-ruth-300x169.jpg"
        }
      }
    }
  }
}
```

![Image field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/image-field-query.png?raw=true)

### File Field <a name="file-field" />

File fields are added to the WPGraphQL Schema as a field with the Type `MediaItem`.

File fields can be queried and a MediaItem will be returned. 

The `MediaItem` type is an Object type that has it's own fields that can be selected. So, instead of _just_ getting the File ID returned and having to ask for the MediaItem object in a follow-up request, we can ask for fields available on the MediaItem Type. For this example, we ask for the `id` and `mediaItemUrl`.

Here, we have a File field named `file` on the Post Edit screen within the "ACF Docs" Field Group.

![File field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/file-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      file {
        id
        mediaItemUrl
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
        "file": {
          "id": "YXR0YWNobWVudDozMjQ=",
          "mediaItemUrl": "http://acf2.local/wp-content/uploads/2020/03/little-ceasars-receipt-01282020.pdf"
        }
      }
    }
  }
}
```

![File field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/file-field-query.png?raw=true)

### WYSIWYG Editor Field <a name="wysiwyg-field" />

WYSIWYG fields are added to the WPGraphQL Schema as a field with the Type `String`.

WYSIWYG fields can be queried and a String will be returned. 

Here, we have a WYSIWYG field named `wysiwyg` on the Post Edit screen within the "ACF Docs" Field Group.

![WYSIWYG field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/wysiwyg-field-input.png?raw=true)

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

![WYSIWYG field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/wysiwyg-field-query.png?raw=true)


### oEmbed Field <a name="oembed-field" />

oEmbed fields are added to the WPGraphQL Schema as a field with the Type `String`.

oEmbed fields can be queried and a String will be returned. 

Here, we have a oEmbed field named `oembed` on the Post Edit screen within the "ACF Docs" Field Group.

![oEmbed field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/oembed-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      oembed
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
        "oembed": "https://www.youtube.com/watch?v=ZEytXfaWwcc"
      }
    }
  }
}
```

![oEmbed field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/oembed-field-query.png?raw=true)


### Gallery Field <a name="gallery-field" />

Gallery fields are added to the WPGraphQL Schema as a field with the Type of `['list_of' => 'MediaItem']`.

Gallery fields can be queried and a list of MediaItem types will be returned. 

Since the type is a list, we can expect an array to be returned. And since the Type within the list is `MediaItem`, we can ask for fields we want returned for each `MediaItem` in the list. In this case, let's say we want to ask for the `id` of each image and the `sourceUrl`, (size large).

Here, we have a Gallery field named `gallery` on the Post Edit screen within the "ACF Docs" Field Group.

![Gallery field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/gallery-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      gallery {
        id
        sourceUrl(size: LARGE)
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
        "gallery": [
          {
            "id": "YXR0YWNobWVudDoyNTY=",
            "sourceUrl": "http://wpgraphql.local/wp-content/uploads/2020/02/babe-ruth.jpg"
          },
          {
            "id": "YXR0YWNobWVudDoyNTU=",
            "sourceUrl": "http://wpgraphql.local/wp-content/uploads/2020/02/babe-ruth-baseball-986x1024.jpg"
          }
        ]
      }
    }
  }
}
```

![Gallery field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/gallery-field-query.png?raw=true)

### Select Field <a name="select-field" />

Select fields (when configured to _not_ allow mutliple selections) are added to the WPGraphQL Schema as a field with the Type `String`.

Select fields, without multiple selections allowed, can be queried and a String will be returned. 

Here, we have a Select field named `select` on the Post Edit screen within the "ACF Docs" Field Group, and "Choice 1" is selected.

![Select field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/select-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      select
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
        "select": "choice_1"
      }
    }
  }
}
```

![Select field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/select-field-query.png?raw=true)

### Checkbox Field <a name="checkbox-field" />

Checkbox fields are added to the WPGraphQL Schema as a field with the Type `[ 'list_of' => 'String' ]`.

Checkbox fields can be queried and a list (array) of Strings (the selected values) will be returned. 

Here, we have a Checkbox field named `checkbox` on the Post Edit screen within the "ACF Docs" Field Group, and "Choice 1" is selected.

![Checkbox field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/checkbox-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      checkbox
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
        "checkbox": [
          "choice_1"
        ]
      }
    }
  }
}
```

![Checkbox field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/checkbox-field-query.png?raw=true)

### Radio Button Field <a name="radio-button-field" />

Radio Button fields are added to the WPGraphQL Schema as a field with the Type `String`.

Radio Button fields can be queried and a String will be returned.

Here, we have a Radio Button field named `radio_button` on the Post Edit screen within the "ACF Docs" Field Group, and "Choice 2" is selected.

![Radio Button field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/radio-button-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      radioButton
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
        "radioButton": "choice_2"
      }
    }
  }
}
```

![Radio Button field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/radio-button-field-query.png?raw=true)

### Button Group Field <a name="button-group-field" />

Button Group fields are added to the WPGraphQL Schema as a field with the Type `String`.

Button Group fields can be queried and a String will be returned.

Here, we have a Button Group field named `button_group` on the Post Edit screen within the "ACF Docs" Field Group, and "Choice 2" is selected.

![Button Group field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/button-group-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      buttonGroup
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
        "buttonGroup": "choice_2"
      }
    }
  }
}
```

![Radio Button field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/radio-button-field-query.png?raw=true)

### True/False Field <a name="true-false-field" />

True/False fields are added to the WPGraphQL Schema as a field with the Type `Boolean`.

True/False fields can be queried and a String will be returned.

Here, we have a True/False field named `true_false` on the Post Edit screen within the "ACF Docs" Field Group, and "true" is selected.

![True/False field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/true-false-group-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      trueFalse
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
        "trueFalse": true
      }
    }
  }
}
```

![True/False field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/true-false-field-query.png?raw=true)

### Link Field <a name="link-field" />

Link fields are added to the WPGraphQL Schema as a field with the Type `ACF_Link`.

Link fields can be queried and a `ACF_Link` will be returned. The ACF Link is an object with fields that can be selected. 

The available fields on the `ACF_Link` Type are: 

- **target** (String): The target of the link
- **title** (String): The target of the link
- **url** (String): The url of the link

Here, we have a Link field named `link` on the Post Edit screen within the "ACF Docs" Field Group.

![Link field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/link-group-field-input.png?raw=true)

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

![Link field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/link-field-query.png?raw=true)

### Post Object Field <a name="post-object-field" />

Post Object fields are added to the WPGraphQL Schema as a field with a [Union](https://graphql.org/learn/schema/#union-types) of Possible Types the field is configured to allow. 

If the field is configured to allow multiple selections, it will be added to the Schema as a List Of the Union Type.

Since Post Object fields can be configured to be limited to certain Post Types, the Union will represent those Types.

For example, if the Post Object field is configured to allow Posts of the `post` and `page` types to be selected: 

![Post Object field Post Type Config](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/post-object-field-post-type-config.png?raw=true)

Then the Union type for the field will allow `Post` and `Page` types to be returned, as seen in the Schema via GraphiQL:

![Post Object field Union Possible Types](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/post-object-field-possible-types.png?raw=true)

Here, we have a Post Object field named `post_object` on the Post Edit screen within the "ACF Docs" Field Group, configured with the Post "Hello World".

![Post Object field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/post-object-field-input-post.png?raw=true)

As a GraphQL consumer, we don't know in advance if the value is going to be a Page or a Post. 

So we can specify, via GraphQL fragment, what fields we want if the object is a Post, or if it is a Page.

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      postObject {
        __typename
        ... on Post {
          id
          title
          date
        }
        ... on Page {
          id
          title
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
        "postObject": {
          "__typename": "Post",
          "id": "cG9zdDox",
          "title": "Hello world!",
          "date": "2020-02-20T23:12:21"
        }
      }
    }
  }
}
```

![Post Object field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/post-object-field-query-post.png?raw=true)

If the input of the field was saved as a Page, instead of a Post, like so:

![Post Object field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/post-object-field-input-page.png?raw=true)

Then the same query above, would return the following results:

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "postObject": {
          "__typename": "Page",
          "id": "cGFnZToy",
          "title": "Sample Page"
        }
      }
    }
  }
}
```

![Post Object field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/post-object-field-query-page.png?raw=true)

Now, if the field were configured to allow multiple values, the field would be added to the Schema as a `listOf`, returning an Array of the Union.

If the field were set with a value of one Page, and one Post, like so:

![Post Object field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/post-object-field-input-multi.png?raw=true)

Then the results of the same query as above would be: 

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "postObject": [
          {
            "__typename": "Page",
            "id": "cGFnZToy",
            "title": "Sample Page"
          },
          {
            "__typename": "Post",
            "id": "cG9zdDox",
            "title": "Hello world!",
            "date": "2020-02-20T23:12:21"
          }
        ]
      }
    }
  }
}
```

![Post Object field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/post-object-field-query-multi.png?raw=true)

### Page Link Field <a name="page-link-field" />

Page Link fields are added to the WPGraphQL Schema as a field with a [Union](https://graphql.org/learn/schema/#union-types) of Possible Types the field is configured to allow.

Since Page Link fields can be configured to be limited to certain Post Types, the Union will represent those Types.

For example, if the Post Object field is configured to allow Posts of the `post` and `page` types to be selected: 

![Page Link field Post Type Config](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/page-link-field-post-type-config.png?raw=true)

Then the Union type for the field will allow `Post` and `Page` types to be returned, as seen in the Schema via GraphiQL:

![Page Link field Union Possible Types](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/page-link-field-possible-types.png?raw=true)

Here, we have a Page Link field named `page_link` on the Post Edit screen within the "ACF Docs" Field Group, and the value is set to the "Sample Page" page.

![Page Link field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/page-link-field-input-page.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      pageLink {
        __typename
        ... on Post {
          id
          title
          date
        }
        ... on Page {
          id
          title
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
        "pageLink": {
          "__typename": "Page",
          "id": "cGFnZToy",
          "title": "Sample Page"
        }
      }
    }
  }
}
```

![Page Link field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/link-field-query-page.png?raw=true)

Here, we set the value to the "Hello World" Post:

![Page Link field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/page-link-field-input-post.png?raw=true)

And the results of the same query are now: 

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "pageLink": {
          "__typename": "Post",
          "id": "cG9zdDox",
          "title": "Hello world!",
          "date": "2020-02-20T23:12:21"
        }
      }
    }
  }
}
```

![Page Link field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/link-field-query-post.png?raw=true)

### Relationship Field <a name="relationship-field" />

Relationship fields are added to the WPGraphQL Schema as a field with a [Union](https://graphql.org/learn/schema/#union-types) of Possible Types the field is configured to allow.

Since Relationship fields can be configured to be limited to certain Post Types, the Union will represent those Types.

For example, if the Post Object field is configured to allow Posts of the `post` and `page` types to be selected: 

![Relationship field Post Type Config](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/relationship-field-post-type-config.png?raw=true)

Then the Union type for the field will allow `Post` and `Page` types to be returned, as seen in the Schema via GraphiQL:

![Relationship field Union Possible Types](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/relationship-field-possible-types.png?raw=true)

Here, we have a Relationship field named `relationship` on the Post Edit screen within the "ACF Docs" Field Group, and the value is set to "Hello World!" post, and the "Sample Page" page.

![Relationship field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/relationship-field-input.png?raw=true)

This field can be Queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      relationship {
        __typename
        ... on Post {
          id
          title
          date
        }
        ... on Page {
          id
          title
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
        "relationship": [
          {
            "__typename": "Post",
            "id": "cG9zdDox",
            "title": "Hello world!",
            "date": "2020-02-20T23:12:21"
          },
          {
            "__typename": "Page",
            "id": "cGFnZToy",
            "title": "Sample Page"
          }
        ]
      }
    }
  }
}
```

![Relationship field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/relationship-field-query.png?raw=true)

### Taxonomy Field <a name="taxonomy-field" />

The Taxonomy field is added to the GraphQL Schema as a List Of the Taxonomy Type.

For example, if the field is configured to the "Category" taxonomy, then the field in the Schema will be a List of the Category type.

![Taxonomy field Taxonomy Config](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/taxonomy-field-taxonomy-config.png?raw=true)

Here, we have a Taxonomy field named `taxonomy` on the Post Edit screen within the "ACF Docs" Field Group, configured with the Category "Test Category".

![Taxonomy field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/taxonomy-field-input.png?raw=true)

This field can be queried like so: 

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      taxonomy {
        __typename
        id
        name
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
        "taxonomy": [
          {
            "__typename": "Category",
            "id": "Y2F0ZWdvcnk6Mg==",
            "name": "Test Category"
          }
        ]
      }
    }
  }
}
```

![Taxonomy field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/taxonomy-field-query.png?raw=true)

### User Field <a name="user-field" />

User fields are added to the WPGraphQL Schema as a field with a User type.

Here, we have a User field named `user` on the Post Edit screen within the "ACF Docs" Field Group, set with the User "jasonbahl" as the value.

![User field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/user-field-input.png?raw=true)

This field can be queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      user {
        id
        username
        firstName
        lastName
      }
    }
  }
}
```

and the response would look like: 

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "user": {
          "id": "dXNlcjox",
          "username": "jasonbahl",
          "firstName": "Jason",
          "lastName": "Bahl"
        }
      }
    }
  }
}
```

![User field Query with one selection](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/user-field-query.png?raw=true)

If the field is configured to allow multiple selections, it's added to the Schema as a List Of the User type.

Here, we have a User field named `user` on the Post Edit screen within the "ACF Docs" Field Group, set with the User "jasonbahl" as the value.

![User field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/user-field-input-multi.png?raw=true)

and the response to the same query would look like: 

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "user": [
          {
            "id": "dXNlcjox",
            "username": "jasonbahl",
            "firstName": "Jason",
            "lastName": "Bahl"
          },
          {
            "id": "dXNlcjoy",
            "username": "WPGraphQL",
            "firstName": "WP",
            "lastName": "GraphQL"
          }
        ]
      }
    }
  }
}
```
![User field Query with multiple selections](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/user-field-query-multiple.png?raw=true)

### Google Map Field <a name="google-map-field" />

Google Map fields are added the WPGraphQL Schema as the `ACF_GoogleMap` Type. 

The `ACF_GoogleMap` Type has fields that expose location data. The available fields are:

- **city** (String): The city associated with the location
- **country** (String): The country associated with the location
- **countryShort** (String): The country abbreviation associated with the location
- **latitude** (String): The latitude associated with the location
- **longitude** (String): The longitude associated with the location
- **placeId** (String): Place IDs uniquely identify a place in the Google Places database and on Google Maps.
- **postCode** (String): The post code associated with the location
- **state** (String): The state associated with the location
- **stateShort** (String): The state abbreviation associated with the location
- **streetAddress** (String): The street address associated with the location
- **streetName** (String): The street name associated with the location
- **streetNumber** (String): The street number associated with the location
- **zoom** (String): The zoom defined with the location

Here, we have a Google Map field named `google_map` on the Post Edit screen within the "ACF Docs" Field Group, set with the Address "1 Infinite Loop, Cupertino, CA 95014, USA" as the value.

![Google Map field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/google-map-field-input.png?raw=true)

This field can be queried in GraphQL like so:

```graphql
{
  post(id: "acf-example-test", idType: URI) {
    acfDocs {
      googleMap {
        streetAddress
        streetNumber
        streetName
        city
        state
        postCode
        countryShort
      }
    }
  }
}

```

and the response would look like: 

```json
{
  "data": {
    "post": {
      "acfDocs": {
        "googleMap": {
          "streetAddress": "1 Infinite Loop, Cupertino, CA 95014, USA",
          "streetNumber": "1",
          "streetName": "Infinite Loop",
          "city": "Cupertino",
          "state": "California",
          "postCode": "95014",
          "placeId": "ChIJHTRqF7e1j4ARzZ_Fv8VA4Eo",
          "countryShort": "US"
        }
      }
    }
  }
}
```

![Google Map field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/google-map-field-query.png?raw=true)

### Date Picker Field <a name="date-picker-field" />

The Date Picker field is added to the WPGraphQL Schema as field with the Type `String`.

Date Picker fields can be queried and a String will be returned. 

Here, we have a Date Picker field named `date_picker` on the Post Edit screen within the "ACF Docs" Field Group, and "13/03/2020" is the date set.

![Date Picker field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/date-picker-field-input.png?raw=true)

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

![Date Picker field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/date-picker-field-query.png?raw=true)

### Date/Time Picker Field <a name="date-time-picker-field" />

The Date/Time Picker field is added to the WPGraphQL Schema as field with the Type `String`.

Date/Time Picker fields can be queried and a String will be returned. 

Here, we have a Date/Time Picker field named `date_time_picker` on the Post Edit screen within the "ACF Docs" Field Group, and "20/03/2020 8:15 am" is the value.

![Date Picker field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/date-time-picker-field-input.png?raw=true)

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

![Date/Time Picker field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/date-time-picker-field-query.png?raw=true)

### Time Picker Field <a name="time-picker-field" />

The Time Picker field is added to the WPGraphQL Schema as field with the Type `String`.

Time Picker fields can be queried and a String will be returned. 

Here, we have a Time Picker field named `time_picker` on the Post Edit screen within the "ACF Docs" Field Group, and "12:30 am" is the value.

![Time Picker field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/time-picker-field-input.png?raw=true)

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

![Time Picker field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/time-picker-field-query.png?raw=true)

### Color Picker Field <a name="color-picker-field" />

The Color Picker field is added to the WPGraphQL Schema as field with the Type `String`.

Color Picker fields can be queried and a String will be returned. 

Here, we have a Color Picker field named `color_picker` on the Post Edit screen within the "ACF Docs" Field Group, and "#dd3333" is the value.

![Color Picker field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/color-picker-field-input.png?raw=true)

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

![Color Picker field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/color-picker-field-query.png?raw=true)

### Message Field <a name="message-field" />

Message fields are not currently supported.

### Accordion Field <a name="accordion-field" />

Accordion Fields are not currently supported.

### Tab Field <a name="tab-field" />

Tab fields are not currently supported.

### Group Field <a name="group-field" />

Group Fields are added to the WPGraphQL Schema as fields resolving to an Object Type named after the Group.

Here, we have a Group field named `group` on the Post Edit screen within the "ACF Docs" Field Group. Within the "group" field, we have a Text Field named `text_field_in_group` and a Text Area field named `text_area_field_in_group`

![Group field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/group-field-input.png?raw=true)

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

![Group field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/group-field-query.png?raw=true)

### Repeater Field <a name="repeater-field" />

Repeater Fields are added to the Schema as a List Of the Type of group that makes up the fields. 

For example, we've created a Repeater Field that has a Text Field named `text_field_in_repeater` and an Image Field named `image_field_in_repeater`.

Here, the Repeater Field is populated with 2 rows:
- Row 1: 
  - Text Field: Text Value 1
  - Image: 256
- Row 2:
  - Text Field: Text Value 2
  - Image: 255

![Repeater field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/repeater-field-input.png?raw=true)

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

![Repeater field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/repeater-field-query.png?raw=true)

### Flexible Content Field <a name="flexible-content-field" />

The Flexible Content is a powerful ACF field that allows for groups of fields to be organized into "Layouts". 

These Layouts can be made up of other types of fields, and can be added and arranged in any order. 

Flexible Content Fields are added to the WPGraphQL Schema as a List Of [Unions](https://graphql.org/learn/schema/#union-types). 

The Union for a Flex Field is made up of each Layout in the Flex Field as the possible Types. 

In our example, we've created a Flex Field with 3 layouts named "Layout 1", "Layout 2" and "Layout 3". In the Schema, we can see the Flex Field Union's Possible Types are these 3 layouts.

![Flex Fields Schema Union Possible Types](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/flex-field-union-possible-types.png?raw=true)

Each of these Layout types will contain the fields defined for the layout and can be queried like fields in any other Group. 

Here's an example of a Flex Field named `flexible_content`, with 3 layouts:

- Layout 1
  - Text field named "text"
  - Text field named "another_text_field"
- Layout 2
  - Image field named "image"
- Layout 3
  - Gallery field named "gallery"
  
Above are the possible layouts and their fields. These layouts can be added and arranged in any order. While we, as a GraphQL consumer, don't know ahead of time what order they will be in, we _do_ know what the possibilities are. 

Here's an example of a Flex Field named `flexible_content` with the values saved as "Layout One", "Layout Two" and "Layout Three", in that order, all populated with their respective fields. 

![Flex field in the Edit Post screen](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/flex-field-input.png?raw=true)

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

![Flex field Query](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/flex-field-query.png?raw=true)

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

![Flex field Query 2](https://github.com/wp-graphql/wp-graphql-acf/blob/master/docs/img/flex-field-query2.png?raw=true)

### Clone Field <a name="clone-field" />

The clone field is not fully supported (yet). We plan to support it in the future.

## Location Rules <a name="location-rules" />

Advanced Custom Fields field groups are added to the WordPress dashboard by being assinged "Location Rules". 

WPGraphQL for Advanced Custom Fields uses the Location Rules to determine where in the GraphQL Schema the field groups/fields should be added to the Schema. 

For example, if a Field Group were assigned to "Post Type is equal to Post", then the field group would show in the WPGraphQL Schema on the `Post` type, allowing you to query for ACF fields of the Post, anywhere you can interact with the `Post` type in the Schema. 

### Supported Locations

@todo: Document supported location rules and how they map from ACF to the WPGraphQL Schema

### Why aren't all location rules supported?
You might notice that some location rules don't add fields to the Schema. This is because some location rules are based on context that doesn't exist when the GraphQL Schema is generated. 

For example, if you have a location rule to show a field group only on a specific page, how would that be exposed the the Schema? There's no Type in the Schema for just one specific page.  

If you're not seeing a field group in the Schema, look at the location rules, and think about _how_ the field group would be added to a Schema that isn't aware of context like which admin page you're on, what category a Post is assigned to, etc. 

If you have ideas on how these specific contextual rules should be handled in WPGraphQL, submit an issue so we can consider how to best support it!
