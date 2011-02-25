# HitCounter2

## Summary

Counts the hits per request URI from web server logs.

## Data Format

Expects Apache Web Server log files as input.

## Implementation

Uses a Mapper implemented in PHP and Hadoop Streaming's "aggregate" package as the Reducer (using operation "LongValueSum").
