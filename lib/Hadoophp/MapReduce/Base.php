<?php

namespace Hadoophp\MapReduce;

/**
 * Base class all mappers and reducers can extend from.
 */
abstract class Base
{
	/**
	 * The input stream handle.
	 */
	private $handle;
	
	/**
	 * The default pattern for generating output (for mappers or reducers).
	 */
	protected $outputPattern = "%s\t%s\n";
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->handle = fopen('php://stdin', 'r');
	}
	
	/**
	 * Read a complete record from the input.
	 * This default implementation reads a line and trims the trailing newline.
	 */
	protected function read() {
		$retval = fgets($this->handle);
		if($retval !== false) {
			return rtrim($retval, "\n");
		}
		return false;
	}
	
	/**
	 * Base splitter function.
	 * Splits a record into key and value; this default implementation assumes a tab character as the separator.
	 *
	 * @param      string The line read by the read() method.
	 */
	protected function split($line)
	{
		return explode("\t", $line, 2);
	}
	
	/**
	 * Emit output.
	 * Uses the given key and value and formats the defined output pattern using these two arguments.
	 *
	 * @see        Hadoophp\MapReduce\Base::$outputPattern
	 *
	 * @param      string The key to emit with.
	 * @param      string The value to emit with.
	 */
	protected function emit($key, $value)
	{
		echo sprintf($this->outputPattern, $key, $value);
	}
	
	/**
	 * Emit a counter value for statistical purposes.
	 * Expects a group name, a counter name, and the amount to increment by.
	 *
	 * @param      string The name of the group the counter belongs to.
	 * @param      string The name of the counter to operate on.
	 * @param      int    An optional amount to increment the counter by (default: 1).
	 */
	protected function emitCounter($group, $counter, $amount = 1)
	{
		file_put_contents('php://stderr', sprintf("reporter:counter:%s,%s,%d\n", $group, $counter, $amount));
	}
	
	/**
	 * Emit a status message for logging purposes.
	 *
	 * @param      string The message to produce.
	 */
	protected function emitStatus($message)
	{
		file_put_contents('php://stderr', sprintf("reporter:status:%s\n", $message));
	}
	
	/**
	 * Main handler function invoked by the runner.
	 * To be implemented by more specific Mapper and Reducer implementations.
	 */
	abstract public function handle();
}

?>