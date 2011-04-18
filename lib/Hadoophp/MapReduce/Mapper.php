<?php

namespace Hadoophp\MapReduce;

require_once('Hadoophp/MapReduce/Base.php');

/**
 * Base Mapper class.
 */
abstract class Mapper extends Base
{
	public function __construct()
	{
		parent::__construct();
		
		$this->inputFieldSeparator = isset($_SERVER['stream_map_input_field_separator']) ? $_SERVER['stream_map_input_field_separator'] : "\t";
		$this->outputFieldSeparator = isset($_SERVER['stream_map_output_field_separator']) ? $_SERVER['stream_map_output_field_separator'] : "\t";
		// hard-code
		$this->inputKeyFields = 1;
		// input fields can't be configured, but "ignoreKey" (only for TextInputWriter, which we assume is the default) plays a role
		if(!isset($_SERVER['stream_map_input_writer_class']) || $_SERVER['stream_map_input_writer_class'] == 'org.apache.hadoop.streaming.io.TextInputWriter') {
			// if ignoreKey is true or if it's not set and we're using TextInputFormat (it's a default for that one), set to 0
			$isTIF = !isset($_SERVER['mapred_input_format_class']) || $_SERVER['mapred_input_format_class'] == 'org.apache.hadoop.mapred.TextInputFormat';
			if(
				(!isset($_SERVER['stream_map_input_ignoreKey']) && $isTIF) ||
				(isset($_SERVER['stream_map_input_ignoreKey']) && $_SERVER['stream_map_input_ignoreKey'] == 'true')
			) {
				$this->inputKeyFields = 0;
			}
		}
		$this->outputKeyFields = isset($_SERVER['stream_num_map_output_key_fields']) ? (int)$_SERVER['stream_num_map_output_key_fields'] : 1;
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