<?php

namespace Hadoophp\MapReduce;

require_once('Hadoophp/MapReduce/Base.php');

/**
 * Base Mapper class.
 */
abstract class Mapper extends Base
{
	/**
	 * Default splitter function for input.
	 * Hadoop streaming sends just the line unless configured otherwise, so there is no key by default.
	 *
	 * @param      string The input, typically a line.
	 *
	 * @return     array An array consisting of a key and a value.
	 */
	protected function split($line)
	{
		return array(null, $line);
	}
	
	/**
	 * Main handler function, invoked by the runner.
	 * Will hand each input fragment to the map() method.
	 */
	public function handle()
	{
		while(($line = $this->read()) !== false) {
			$kv = $this->split($line);
			if(!$kv || count($kv) != 2) {
				continue;
			}
			list($key, $value) = $kv;
			
			$this->map($key, $value);
		}
	}
	
	/**
	 * The mapper implementation.
	 *
	 * @param      mixed The key for the given record.
	 * @param      mixed The value of the given record.
	 */
	abstract protected function map($key, $value);
}

?>