<?php

require_once('Hadoophp/MapReduce/Mapper.php');
require_once('Hadoophp/MapReduce/Util.php');

class Mapper extends \Hadoophp\MapReduce\Mapper
{
	protected function map($key, $value)
	{
		if($log = \Hadoophp\MapReduce\Util::parseApacheLogLine($value)) {
			$this->emit($log['request_uri'], 1);
			// diagnostics
			if(HADOOPHP_DEBUG) {
				$this->emitCounter('com.github.dzuelke.hadoophp.examples.HitsByUri', 'MAP_PARSED_RECORDS');
			}
		} else {
			// diagnostics
			if(HADOOPHP_DEBUG) {
				$this->emitCounter('com.github.dzuelke.hadoophp.examples.HitsByUri', 'MAP_FAILED_RECORDS');
			}
		}
	}
}

?>