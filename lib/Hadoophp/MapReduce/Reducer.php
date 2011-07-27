<?php

namespace Hadoophp\MapReduce;

require_once('Hadoophp/MapReduce/Base.php');

abstract class Reducer extends Base implements \Iterator
{
	protected $previousKey = null;
	protected $currentKey = null;
	protected $currentValue = null;
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->inputFieldSeparator = isset($_SERVER['stream_reduce_input_field_separator']) ? $_SERVER['stream_reduce_input_field_separator'] : "\t";
		$this->outputFieldSeparator = isset($_SERVER['stream_reduce_output_field_separator']) ? $_SERVER['stream_reduce_output_field_separator'] : "\t";
		// yes, this is correct
		$this->inputKeyFields = isset($_SERVER['stream_num_map_output_key_fields']) ? (int)$_SERVER['stream_num_map_output_key_fields'] : 1;
		$this->outputKeyFields = isset($_SERVER['stream_num_reduce_output_key_fields']) ? (int)$_SERVER['stream_num_reduce_output_key_fields'] : 1;
		
		// init
		$this->readAhead();
		$this->previousKey = $this->currentKey;
	}
	
	/**
	 * Main handler function, invoked by the runner.
	 * Will hand each key and an iterator for all corresponding values to the reduce() method.
	 */
	public function handle()
	{
		while(($key = $this->getCurrentKey()) !== null) {
			$this->reduce($key, $this);
			// make sure that if a reducer exits prematurely, we scan ahead to the next key
			while($this->valid()) {
				$this->next();
			}
			
			if(HADOOPHP_DEBUG) {
				$this->emitCounter('SkippingTaskCounters', 'ReduceProcessedGroups'); // defined in org.apache.hadoop.mapred.SkipBadRecords
			}
			
			$this->reset();
		}
	}
	
	/**
	 * Read the next input chunk.
	 */
	private function readAhead()
	{
		// reset
		$this->currentKey = $this->currentValue = null;
		// scan forward to the next valid entry
		while(true) {
			// ingest
			$chunk = $this->read();
			if($chunk === false) {
				// EOF
				return;
			} else {
				$kv = $this->split($chunk);
				if($kv && count($kv) == 2) {
					list($this->currentKey, $this->currentValue) = $kv;
					return;
				}
			}
		}
	}
	
	/**
	 * Reset the iterator so another foreach() run for the next key will be possible.
	 */
	public function reset()
	{
		// allow iteration again
		$this->previousKey = $this->currentKey;
	}
	
	/**
	 * @see        Iterator::current()
	 */
	public function current()
	{
		return $this->currentValue;
	}
	
	/**
	 * Casts the key to string so it always works in foreach()es
	 * @see        Iterator::key()
	 */
	public function key()
	{
		// must not cast null to a string, otherwise handle() will reduce forever
		return $this->currentKey === null ? $this->currentKey : (string)$this->currentKey;
	}
	
	/**
	 * Return the current key object (unlike key(), this doesn't cast to string).
	 */
	public function getCurrentKey()
	{
		return $this->currentKey;
	}
	
	/**
	 * @see        Iterator::next()
	 */
	public function next()
	{
		$this->readAhead();
	}
	
	/**
	 * @see        Iterator::rewind()
	 */
	public function rewind()
	{
		// nop
	}
	
	/**
	 * @see        Iterator::valid()
	 */
	public function valid()
	{
		// make sure iteration ends once the key changes; reset() will take care of changing previousKey so that iteration works once again
		if(isset($_SERVER['mapred_partitioner_class']) && $_SERVER['mapred_partitioner_class'] == 'org.apache.hadoop.mapred.lib.KeyFieldBasedPartitioner' && isset($_SERVER['mapred_text_key_partitioner_options'])) {
			$options = $_SERVER['mapred_text_key_partitioner_options'];
			$ranges = explode(' ', $options);
			$ck = $this->currentKey ? $this->currentKey->getParts() : array();
			$pk = $this->previousKey ? $this->previousKey->getParts() : array();
			foreach($ranges as $range) {
				if(preg_match('#^-k(?P<from>\d+(,(?P<to>\d+))?)$#', $range, $matches)) {
					$from = $matches['from'] - 1;
					$to = (isset($matches['to']) ? $matches['to'] : $matches['from']) - 1;
					if(array_slice($ck, $from, $to-$from+1) != array_slice($pk, $from, $to-$from+1)) {
						return false;
					}
				}
			}
			return true;
		} else {
			return $this->currentKey == $this->previousKey;
		}
	}
	
	/**
	 * The reducer implementation.
	 *
	 * @param      mixed       The key for the given records.
	 * @param      Traversable An iterator delivering the values for the current key.
	 */
	abstract protected function reduce($key, \Traversable $values);
}

?>