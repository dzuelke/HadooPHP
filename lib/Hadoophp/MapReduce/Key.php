<?php

namespace Hadoophp\MapReduce;

/**
 * Key class.
 */
class Key implements \ArrayAccess
{
	protected $parts = array();
	protected $separator = "\t";
	protected $count = 1;
	
	public function __construct(array $parts, $separator = "\t", $count = 1)
	{
		$this->parts = $parts;
		$this->separator = $separator;
		$this->count = $count;
	}
	
	public function __toString()
	{
		return implode($this->separator, $this->parts);
	}
	
	public function offsetExists($offset)
	{
		return isset($this->parts[$offset]);
	}
	
	public function offsetGet($offset)
	{
		return isset($this->parts[$offset]) ? $this->parts[$offset] : null;
	}
	
	public function offsetSet($offset, $value)
	{
		// nop
	}
	
	public function offsetUnset($offset)
	{
		// nop
	}
	
	public function getParts()
	{
		return $this->parts;
	}
	
	public function valid()
	{
		return $this->count == count($this->parts);
	}
}