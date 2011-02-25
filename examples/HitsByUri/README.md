# HitsByUri

## Summary

Counts the hits per request URI from web server logs.

## Data Format

Expects Apache Web Server log files as input.

## Implementation

Uses a Mapper and Reducer implemented in PHP. Does not make use of a Combiner (although it could).
It also uses counters to keep track of the number of parsed (and failed) lines.