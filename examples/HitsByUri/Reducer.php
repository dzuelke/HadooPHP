<?php

require_once('Hadoophp/MapReduce/Reducer.php');

class Reducer extends \Hadoophp\MapReduce\Reducer
{
	public function reduce($key, Traversable $values)
	{
		$sum = 0;
		
		foreach($values as $value) {
			$sum += $value;
		}
		
		$this->emit($key, $sum);
	}
}

?>