<?php namespace Ideil\GenericFile\Interpolator;

use Ideil\GenericFile\Resources\File;

class Interpolator {

	/**
	 * Constructor method.
	 *
	 * @param Ideil\GenericFile\Interpolator\Handlers $handlers_base
	 * @param Ideil\GenericFile\Interpolator\Handlers $handlers_filters
	 */
	public function __construct(Handlers $handlers_base, Handlers $handlers_filters)
	{
		$this->handlers_base    = $handlers_base;

		$this->handlers_filters = $handlers_filters;
	}

	/**
	 * Extract interpolation directives and filters from input string
	 *
	 * @param string $str
	 *
	 * @return array
	 */
	protected function parseInterpolationString($str)
	{
		$parsed = [];

		if ( ! preg_match_all('~\{([a-z\d_\-\|\#]+?)\}~i', $str, $matches))
		{
			return $parsed;
		}

		foreach ($matches[1] as $key => $value)
		{
			$parsed[$matches[0][$key]] = explode('|', $value);
		}

		return $parsed;
	}

	/**
	 * Call directive handler by name from passed handlers list with argument
	 *
	 * @param  string $directive_key
	 * @param  File $file
	 *
	 * @return mixed
	 */
	protected function handleDirective($directive_key, File $file)
	{
		return $this->handlers_base->call($directive_key, $file);
	}

	/**
	 * Call filter handlers by array of names from passed handlers list with argument
	 *
	 * @param  array $data
	 * @param  mixed $arg
	 *
	 * @return mixed
	 */
	protected function handleSubdirectives(array $data, $arg)
	{
		if (count($data) <= 1)
		{
			return $arg;
		}

		// remove base directive

		array_shift($data);

		// call handler for each filter

		foreach ($data as $value)
		{
			$arg = $this->handlers_filters->call($value, $arg);
		}

		return $arg;
	}

	/**
	 * Make path to file using path pattern
	 *
	 * @param  string $str
	 * @param  File $file
	 *
	 * @return InterpolatorResult
	 */
	public function resolveStorePath($str, File $file)
	{
		$cache  = [];

		$parsed = $this->parseInterpolationString($str);

		foreach ($parsed as $key => $directive_with_filters)
		{
			$directive_key_dirty = reset($directive_with_filters);
			$directive_key = strstr($directive_key_dirty, '#', true) ?: $directive_key_dirty;

			if ( ! isset($cache[$directive_key]))
			{
				$cache[$directive_key] = $this->handleDirective($directive_key, $file);
			}

			$result = $this->handleSubdirectives($directive_with_filters,
				$cache[$directive_key]);

			$str = str_replace($key, $result, $str);
		}

		return new InterpolatorResult($cache, $str);
	}

	/**
	 * Make url to file using path pattern
	 *
	 * @param  string  $str
	 * @param  array  $model
	 * @param  array  $model_map
	 *
	 * @return InterpolatorResult
	 */
	public function resolvePath($str, $model, $model_map = array())
	{
		$parsed = $this->parseInterpolationString($str);

		foreach ($parsed as $key => $directive_with_filters)
		{
			$directive = reset($directive_with_filters);

			$field = isset($model_map[$directive]) ? $model_map[$directive] : $directive;

			$result = $this->handleSubdirectives($directive_with_filters,
				$model[$field], $this->handlers_filters);

			$str = str_replace($key, $result, $str);
		}

		return new InterpolatorResult($model, $str);
	}

}
