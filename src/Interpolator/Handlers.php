<?php namespace Ideil\GenericFile\Interpolator;

use UnexpectedValueException;

abstract class Handlers {

	/**
	 * Contains list of callable handlers.
	 *
	 * @var array
	 */
	protected $handlers = [];

	/**
	 * Constructor method.
	 *
	 * @param array $handlers
	 */
	public function __construct(array $handlers = array())
	{
		// extend default handlers

		$this->handlers = $handlers + $this->getDeafultHandlers();

	}

	/**
	 * Return default handlers
	 *
	 * @return array
	 */
	abstract public function getDeafultHandlers();

	/**
	 * Return default handlers
	 *
	 * @param string $name
	 * @param mixed $input
	 * @return mixed
	 */
	public function call($name, $input)
	{
		$normalized_name = strtolower($name);

		if ( ! isset($this->handlers[$normalized_name]))
		{
			throw new UnexpectedValueException('No required handler: ' . $name, 1);
		}

		return $this->handlers[$normalized_name]($input);
	}

}
