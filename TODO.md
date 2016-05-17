TODO Part 1:
- Allow dimension map (in array format) to be sent in query as an alternative to just dimension name. Allow properties like "scale", "size" and possibly "filter". Let's discuss "filter" more and not implement it just yet as we can apply filter at many levels so it is better to apply filter at the query or metric level.
- Pass context as query param to all endpoints: /api/analytics/source, /api/analytics/source/<datasource>, /api/analytics/source/<datasource>/dimension, /api/analytics/source/<datasource>/metric
- Write a test for Custom Filters. Ensure it works end to end for API.
- Add ability to set tags on a dimension and metrics
- Add ability to set Type on filter source, ie string, numeric,date etc.
- Create endpoint to just get dimensions /api/analytics/source/<datasource>/dimension . Add pagination and filtering by tag.
- Create endpoint to just get metrics /api/analytics/source/<datasource>/metric . Add pagination and filtering by tag.
- Check/Optimize code so that when you query, you just build the dimensions that are required. 
- Check/Optimize code so that when you populate "_info" for dimension buckets, you don't call "GET" for each key one by one. Better to batch those calls.

TODO:
- Order of buckets ?
- Filter by Id and dimension by some metric value
- Children Aggregations
- Script Dimension

- Support very large list of metrics and dimensions
- Support context on other endpoints like /source
- Change $path to $nestedPath
- tagged metrics or dimensions
- search dimensions and metrics (name and tags)

May be:
- Remove Container from Analytics. If we do, we can't create dynamic metrics. May be give EntityManager only?

Done:
- Metric filters
- in-app Metrics / Post processing
- Top level filters
- Make flattened, tabular view calculation lazy
- Replace stat aggregation with sum, avg aggregations
- Add Range Dimension
- Pass Container to Analytics classes
- Nested Aggregations
- Implement Filter Sources
- Api for metrics, dimensions and filters
- Size for Term Stats
- API Path
- Filter field names
- Add auto include metrics
- Datatable
- Return docs
- Custom Filter Classes (Parent/Child Filters)
- Add Type of metrics (percentage, value, etc)
- Reverse Nested Aggregations
- Pass context from client to the analytics class