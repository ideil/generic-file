<?php namespace Ideil\GenericFile;

use Exception;

use SplFileInfo;

/**
 * Easy file upload management.
 *
 * This package was inspired by Stapler
 *
 * @package Ideil/GenericFile
 * @version v0.1.6
 * @author Sergiy Litvinchuk <sergiy.litvinchuk@gmail.com>
 * @link
 */

class GenericFile {

	/**
	 * An instance of the interpolator class for processing interpolations.
	 *
	 * @var \Ideil\GenericFile\Interpolator\Interpolator
	 */
	protected $interpolator;

	/**
	 * Configuration
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Constructor method.
	 */
	public function __construct($config)
	{
		// store config

		$this->config = $config;

		// make interpolator

		$this->interpolator = new Interpolator\Interpolator(

			new Interpolator\HandlersBase(
				$this->getConfig('handlers-base', [])),

			new Interpolator\HandlersFilters(
				$this->getConfig('handlers-filters', []))

		);
	}

	/**
	 * @param  string $key
	 *
	 * @return mixed
	 */
	public function getConfig($key, $default = null)
	{
		$array = $this->config;

		if (is_null($key))
			return $array;

		if (isset($array[$key]))
			return $array[$key];

		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_array($array) || ! array_key_exists($segment, $array))
			{
				return $default;
			}

			$array = $array[$segment];
		}

		return $array;
	}

	/**
	 * Can delete uploaded files physically
	 *
	 * @return boolean
	 */
	public function canRemoveFiles()
	{
		return (boolean) $this->getConfig('store.prevent_deletions', false);
	}

	/**
	 * Get root path to store files
	 *
	 * @return string
	 */
	protected function getStoreRootPath()
	{
		$public_path = $this->getConfig('store.public_path', '');

		$public_path = rtrim($public_path, '/');

		if ($public_path)
		{
			return $public_path;
		}

		throw new Exception('No public path configured', 1);
	}

	/**
	 * Move uploaded file to path by pattern and update model
	 *
	 * @param  SplFileInfo $file
	 * @param  string|null $path_pattern
	 *
	 * @return string|null
	 */
	public function store(SplFileInfo $file, $path_pattern = null)
	{
		// inperpolate path_pattern using $file
		// and get interpolator result object

		$interpolated = $this->interpolator->resolveStorePath(
			$path_pattern ?: $this->getConfig('store.path_pattern', ''), $file);

		// file will be moved to interpolated path
		// and configured public_path as root

		$target = $this->getStoreRootPath() . '/' . ltrim($interpolated, '/');

		if ( ! file_exists($target))
		{
			// try use built in move method (Laravel)

			if (method_exists($file, 'move'))
			{
				$file->move(dirname($target), basename($target));

				return $interpolated;
			}

			// else use move_uploaded_file function

			if ( ! @move_uploaded_file($this->getPathname(), $target))
			{
				$error = error_get_last();

				throw new Exception(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error['message'])));
			}
		}

		return $interpolated;
	}

	/**
	 * Make url to stored file
	 *
	 * @param array|object $model
	 * @param string|null  $path_pattern
	 * @param array        $model_map
	 *
	 * @return string
	 */
	public function url($model, $path_pattern = null, array $model_map = array())
	{
		$pattern = $path_pattern
			?: $this->getConfig('http.path_pattern', '');

		$domain = $this->getConfig('http.domain', '');

		$path = $this->interpolator->resolvePath($pattern, $model, $model_map)->getResult();

		$path = rtrim($domain, '/') . '/' . ltrim($path, '/');

		return $path;
	}

	/**
	 * Full path to stored file
	 *
	 * @param array|object $model
	 * @param string|null  $path_pattern
	 * @param array        $model_map
	 *
	 * @return string
	 */
	public function path($model, $path_pattern = null, array $model_map = array())
	{
		$pattern   = $path_pattern ?: $this->getConfig('store.path_pattern', '');

		$path = $this->interpolator->resolvePath($pattern, $model, $model_map)->getResult();

		$path = $this->getStoreRootPath() . '/' . ltrim($path, '/');

		return $path;
	}

	/**
	 * Delete stored file
	 *
	 * @param array|Illuminate\Database\Eloquent\Model $model
	 * @param string|null $path_pattern
	 *
	 * @return string
	 */
	public function delete($model, $path_pattern = null)
	{
		if ( ! $this->canRemoveFiles())
		{
			return null;
		}

		$filepath = $this->path($model, $path_pattern);

		if ($filepath && file_exists($filepath))
		{
			return unlink($filepath);
		}

		return false;
	}

}
