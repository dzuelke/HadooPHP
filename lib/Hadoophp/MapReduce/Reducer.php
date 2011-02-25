<?php

namespace Hadoophp\MapReduce;

require_once('Hadoophp/MapReduce/Base.php');

abstract class Reducer extends Base
{
	/**
	 * Main handler function, invoked by the runner.
	 * Will hand each key and an array of all values to the reduce() method.
	 *
	 * @TODO       This needs to be rewritten to use iterators for much improved memory efficiency.
	 */
	public function handle()
	{
		$values = array();
		$lastkey = null;
		while(($line = $this->read()) !== false) {
			$kv = $this->split($line);
			if(!$kv || count($kv) != 2) {
				// we need a key and value pair, but we didn't get that, so we skip this record
				continue;
			}
			list($key, $value) = $kv;
			
			if($key != $lastkey) {
				// new key, time to reduce before proceeding
				if($lastkey !== null) {
					// it's not the very first iteration
					$this->reduce($lastkey, $values);
					// reset values array
					$values = array();
				}
				
				// remember new key
				$lastkey = $key;
			}
			
			$values[] = $value;
		}
		
		// one last reduce if necessary
		if($values && $lastkey !== null) {
			$this->reduce($lastkey, $values);
		}
	}
	
	/**
	 * The mapper implementation.
	 *
	 * @param      mixed The key for the given records.
	 * @param      array An array of values for the given record.
	 *
	 * @TODO       This will soon change so $values is an iterator.
	 */
	abstract protected function reduce($key, $values);
}

?>