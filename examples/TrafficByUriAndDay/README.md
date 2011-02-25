# TrafficByUriAndDay

## Summary

Counts the sum of bytes per request URI and day from web server logs.

## Data Format

Expects Apache Web Server log files as input.

## Implementation

Uses a Mapper implemented in PHP and Hadoop Streaming's "aggregate" package as the Reducer and Combiner (using operation "LongValueSum").

The keys it produces consist of two fields (uri and day); ARGUMENTS contains the necessary flags to make mapred aware of this.
