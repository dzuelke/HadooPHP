<?php

require_once('Hadoophp/MapReduce/Mapper.php');
require_once('Hadoophp/MapReduce/Util.php');

class Mapper extends \Hadoophp\MapReduce\Mapper
{
	protected function map($key, $value)
	{
		if($log = \Hadoophp\MapReduce\Util::parseApacheLogLine($value)) {
			$this->emit(
				// composite key
				sprintf("LongValueSum:%s\t%s", $log['request_uri'], $log['datetime']->format('Y-m-d')),
				$log['length']
			);
		}
	}
}

?>