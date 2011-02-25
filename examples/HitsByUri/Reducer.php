<?php

require_once('Hadoophp/MapReduce/Reducer.php');

class Reducer extends \Hadoophp\MapReduce\Reducer
{
	public function reduce($key, $values)
	{
		$this->emit($key, array_sum($values));
	}
}

?>