# HadooPHP is a framework for writing Hadoop Streaming jobs in PHP

## Features

* Packages jobs as [http://php.net/phar](PHAR) files for speedy and convenient deployment.
 * Also generates shell scripts to invoke a job (requires `$HADOOP_HOME` to be defined).
* Abstracted input parsing and splitting
* Supports custom arguments for the Hadoop invocation


### Planned Features

* Iterators for reducers (reduces memory footprint over the current array implementation).
* Unit testing capabilities (Mappers and Reducers could be tested locally).
* Support for any sort of input (might already work; it hasn't really been tested with input like XML, for example).
* Automatic detection of streaming settings if possible (so it knows about the configured key size et cetera).


## Examples

The `examples` sub-directory contains a number of examples.


## Writing Jobs

### Prerequisites

* A functioning Hadoop installation, with namenode, jobtracker and all other components running.
 * If you are developing locally, follow the Hadoop Quick Start Guide to set up pseudo-distributed mode.
* The PHAR PHP extension must be enabled, and `phar.readonly` must be set to `0` in php.ini if you want to compile jobs.


### Job Creation

Create a folder (this folder name will be the job name later on), with a `Mapper.php` containing the mapper class, a `Reducer.php` containing a reducer class (if desired), and, if you want, an `ARGUMENTS` file with additional arguments.

***Note: when you have an ARGUMENTS file, you must include full `-mapper` and `-reducer` commands, see the examples. Any `-D` flags in ARGUMENTS must also precede any other switches such as `-mapper` or `-file`.***

### Job Compilation

Assuming your job (and thus folder) name is "TpsReportCount", run:

    bin/compile.sh TpsReportCount <BUILDDIR>

***Note: the build dir must exist and be writeable.***

### Job Invocation

Assuming your job name is "TpsReportCount", run:

    path/to/builddir/TpsReportCount.sh <HDFSINPUTDIR> <HDFSOUTPUTDIR>
