<?php namespace Ideil\GenericFile\Interpolator;

class InterpolatorResult {

	/**
	 * Constructor method.
	 *
	 * @param mixed $data
	 * @param mixed $result
	 */
	public function __construct($data, $result)
	{
		$this->data = $data;

		$this->result = $result;
	}

	/**
	 * Return data
	 *
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Return result
	 *
	 * @return mixed
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->result;
	}
}
