---
title: Querying in GraphQL
---

# Querying in GraphQL

The query input can support all the parameters that you can use in regular 
[Searching](../getting-started/usage/#searching), with the exception that 
`location` only supports a string value. This means if you want to search by 
lat/lng you need to pass them to the `coordinate` input.

```graphql
{
  entries (
    map: {
      unit: Kilometres
      location: "Maidstone, Kent"
      country: "UK"
      radius: 10
      coordinate: {
        lat: 51.27136675686769
        lng: 0.4939985275268555
      }
    }
    section: "locations"
    orderBy: "distance"
  ) {
    title
    ... on locations_locations_Entry {
      map {
        lat
        lng
        distance
        zoom
        address
        parts {
          number
          address
          city
          postcode
          county
          state
          country
        }
      } 
    }
  }
}
```
