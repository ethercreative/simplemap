---
title: Querying in GraphQL
---

# Querying in GraphQL

While the _CraftQL_ plugin has great support for custom arguments for field 
types, Crafts current built-in implementation of GraphQL doesn't. To work 
around this, we've added support for JSON strings within the query argument for 
Map fields. It's not pretty, but it works.

The JSON object can support all the parameters that you can use in regular 
[Searching](../getting-started/usage/#searching).

```graphql
{
  entries (
    map:"{\"location\":\"Maidstone, Kent\", \"country\": \"UK\", \"radius\": 50}"
    section: "locations"
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

As you can see in the example above, the JSON string is escaped within a string
argument. Remember, you can't pass a JSON object to the argument, it has to be 
stringified.
 
If and when Craft give plugins the ability to define their own arguments we'll
switch to using a Map specific type, until then we'll have to make do with 
JSON in a string.
