<?php namespace Ideil\GenericFile\Tools;

use SplFileInfo;

class Hasher {

	use \Ideil\GenericFile\Traits\HashingTrait;

	/**
	 * Make hash from uploaded file.
	 *
	 * @param  SplFileInfo $file
	 * @return string
	 */
	public function file(SplFileInfo $file)
	{
		return substr($this->base(hash_file('sha256', $file->getRealPath(), true)), 0, 32);
	}
}
