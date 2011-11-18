<?php

require_once('Hadoophp/MapReduce/Mapper.php');
require_once('Hadoophp/MapReduce/Util.php');

class Mapper extends \Hadoophp\MapReduce\Mapper
{
	protected function map($key, $value)
	{
		if($log = \Hadoophp\MapReduce\Util::parseApacheLogLine($value)) {
			$this->emit("LongValueSum:" . $log['datetime']->format('H'), 1);
			// diagnostics
			if(HADOOPHP_DEBUG) {
				$this->emitCounter('com.github.dzuelke.hadoophp.examples.HitsByHour', 'MAP_PARSED_RECORDS');
			}
		} else {
			// diagnostics
			if(HADOOPHP_DEBUG) {
				$this->emitCounter('com.github.dzuelke.hadoophp.examples.HitsByHour', 'MAP_FAILED_RECORDS');
			}
		}
	}
}

?>