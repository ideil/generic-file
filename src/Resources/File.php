<?php namespace Ideil\GenericFile\Resources;

use SplFileInfo;
use UnexpectedValueException;

class File extends \Symfony\Component\HttpFoundation\File\File {

	/**
	 * @var string
	 */
	protected $original_name;

	/**
	 * Constructs a new file from the given path.
	 *
	 * @param string|SplFileInfo $pathOrFile
	 * @param string $original_name
	 *
	 */
	public function __construct($pathOrFile, $original_name)
	{
		$this->original_name = $original_name;

		if ($pathOrFile instanceof SplFileInfo)
		{
			parent::__construct($pathOrFile->getRealPath(), false);
		}
		elseif(is_string($pathOrFile))
		{
			parent::__construct($pathOrFile, true);
		}
		else
		{
			throw new UnexpectedValueException('Input value must be string or SplFileInfo');
		}
	}

	/**
	 * Returns the original file name.
	 *
	 * It is extracted from the request from which the file has been uploaded.
	 * Then it should not be considered as a safe value.
	 *
	 * @return string|null The original name
	 *
	 * @api
	 */
	public function getClientOriginalName()
	{
		return $this->original_name;
	}

	/**
	 * Returns the original file extension.
	 *
	 * It is extracted from the original file name that was uploaded.
	 * Then it should not be considered as a safe value.
	 *
	 * @return string The extension
	 */
	public function getClientOriginalExtension()
	{
		return pathinfo($this->original_name, PATHINFO_EXTENSION);
	}

}
