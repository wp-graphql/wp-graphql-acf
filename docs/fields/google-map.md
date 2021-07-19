# Google Map Field

Google Map fields are added to the WPGraphQL Schema as the `ACF_GoogleMap` Type.

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

![Google Map field in the Edit Post screen](../img/map-field-input.png?raw=true)

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

----

- **Previous Field:** [Gallery](./gallery.md)
- **Next Field:** [Group](./group.md)
