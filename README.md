# HadooPHP is a framework for writing Hadoop Streaming jobs in PHP

## Features

* Packages jobs as [PHAR](http://php.net/phar) files for speedy and convenient deployment.
 * Also generates shell scripts to invoke a job (requires `$HADOOP_HOME` to be defined).
* Automatic detection of streaming settings (so it knows about the configured key field separators and lengths et cetera).
* Abstracted input parsing and splitting (only supports line-based input at the moment though).
* Supports custom arguments for the Hadoop invocation.

### Known Issues

* Something like `KeyValueTextInputFormat` does not work, partly because passing it as `-inputformat` sets the property to `StreamingInputFormat` (must investigate why), and `-D mapred.input.format.class` seems to have no effect. Unsure how to fix.

### Planned Features

* Unit testing capabilities (Mappers and Reducers could be tested locally).
* Support for input formats other than TextInputFormat (only line-based stuff works out of the box right now).


## Examples

The `examples` sub-directory contains a number of examples.


## Writing Jobs

### Prerequisites

* A functioning Hadoop installation, with namenode, jobtracker and all other components running.
 * If you are developing locally, follow the Hadoop Quick Start Guide to set up [pseudo-distributed mode](http://hadoop.apache.org/common/docs/r0.20.2/quickstart.html#PseudoDistributed).
* The PHAR PHP extension must be enabled, and `phar.readonly` must be set to `0` in php.ini if you want to compile jobs.


### Job Creation

Create a folder (this folder name will be the job name later on), with a `Mapper.php` containing the mapper class, a `Reducer.php` containing a reducer class (if desired), and, if you want, an `ARGUMENTS` file with additional arguments.

*Note: when you have an ARGUMENTS file, you must include full `-mapper` and `-reducer` commands, see the examples. Any `-D` flags in ARGUMENTS must also precede any other switches such as `-mapper` or `-file`. Every line in ARGUMENTS must be terminated by a backslash because the raw contents of the file are inserted into the generated shell script.*

### Job Compilation

Assuming your job (and thus folder) name is "TpsReportCount", run:

    bin/compile.sh TpsReportCount <BUILDDIR>

*Note: the build dir must exist and be writeable.*

You can pass path names to include in the package and the default timezone to set for scripts to `compile.sh`; invoke it without arguments for usage help. The `--debug` switch enables debug mode which means the `HADOOPHP_DEBUG` constant will be set to true, you can use this to add counter or message emitters to your code that should only run in such a case.

### Job Invocation

Assuming your job name is "TpsReportCount", run:

    path/to/builddir/TpsReportCount.sh <HDFSINPUTPATH...> <HDFSOUTPUTPATH>

*Note: you may pass any number of input path names; the last path name given is the output path where results will be written to.*

You may also pass the path to a Hadoop config dir (equivalent to the `--config` argument of the `hadoop` binary):

    path/to/builddir/TpsReportCount.sh -c path/to/dir/with/remote-cluster-config <HDFSINPUTPATH...> <HDFSOUTPUTPATH>
