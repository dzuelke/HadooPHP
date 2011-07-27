<?php

namespace Hadoophp\MapReduce;

class Util {
	
	public static function parseApacheLogLine($line) {
		if(preg_match('/^(?P<ip>[0-9a-fA-F.:]+)\s(?P<identd>\S+)\s(?P<authuser>\S+)\s\[(?P<timestamp>[^\]]+)\]\s"(?P<request>(?P<request_method>[A-Z]+)\s(?P<request_uri>\S+)\s(?P<request_protocol>\S+))"\s(?P<status>\d+)\s(?P<length>(\d+|-))/', $line, $matches)) {
			$matches['datetime'] = new \DateTime($matches['timestamp']);
			if($matches['length'] == '-') {
				$matches['length'] = '0';
			}
			return $matches;
		}
	}
	
	public static function findMinMax(\Traversable $it, callback $extractor = null) {
		if($extractor === null) {
			$extractor = function($value) {
				return $value;
			};
		}
		
		$min = $max = $extractor($it->current());
		$it->next();
		$value = $extractor($it->current());
		while($it->valid()) {
			if($value > $max) {
				$max = $value;
			} elseif($value < $min) {
				$min = $value;
			}
			$it->next();
			$value = $it->current();
		}
		
		return array($min, $max);
	}
	
}

?>