<?php

namespace Hadoophp\MapReduce;

require_once('Hadoophp/MapReduce/Key.php');

/**
 * Base class all mappers and reducers can extend from.
 */
abstract class Base
{
	/**
	 * The input stream handle.
	 */
	private $handle;
	
	protected $inputFieldSeparator = "\t";
	protected $outputFieldSeparator = "\t";
	protected $inputKeyFields = 1;
	protected $outputKeyFields = 1;
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->handle = fopen('php://stdin', 'r');
		
		$this->reporterPrefix = isset($_SERVER['stream_stderr_reporter_prefix']) ? $_SERVER['stream_stderr_reporter_prefix'] : 'reporter:';
	}
	
	/**
	 * Read a complete record from the input.
	 * This default implementation reads a line and trims the trailing newline.
	 * Returns the result or false if EOF reached.
	 *
	 * @return     mixed The input record as a string or false if EOF was reached.
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
	 *
	 * @return     array An array with key and value, or null for malformed input.
	 */
	protected function split($line)
	{
		if($this->inputKeyFields == 0) {
			// no key
			$key = array(null);
			$value = $line;
		} else {
			$parts = explode($this->inputFieldSeparator, $line, $this->inputKeyFields + 1); // max keyfields + 1 elements
			$key = array_splice($parts, 0, $this->inputKeyFields);
			$value = array_pop($parts); // will be null if there weren't $inputKeyFields+1 parts
		}
		
		return array(new Key($key, $this->inputFieldSeparator, $this->inputKeyFields), $value); // return 
	}
	
	/**
	 * Emit output.
	 * Uses the given key and value and emits using these two arguments.
	 *
	 * @param      mixed  The key (string, array or Key object) to emit with.
	 * @param      string The value to emit with.
	 */
	protected function emit($key, $value)
	{
		if($key instanceof Key) {
			$key = $key->getParts();
		} elseif(!is_array($key)) {
			$key = array($key);
		}
		
		echo implode($this->outputFieldSeparator, $key) . $this->outputFieldSeparator . $value . "\n";
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
		file_put_contents('php://stderr', sprintf("%scounter:%s,%s,%d\n", $this->reporterPrefix, $group, $counter, $amount));
	}
	
	/**
	 * Emit a status message for logging purposes.
	 *
	 * @param      string The message to produce.
	 */
	protected function emitStatus($message)
	{
		file_put_contents('php://stderr', sprintf("%sstatus:%s\n", $this->reporterPrefix, $message));
	}
	
	/**
	 * Main handler function invoked by the runner.
	 * To be implemented by more specific Mapper and Reducer implementations.
	 */
	abstract public function handle();
}

?>