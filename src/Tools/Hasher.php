<?php namespace Ideil\GenericFile\Tools;

use SplFileInfo;

class Hasher {

	use \Ideil\GenericFile\Traits\HashingTrait;

	/**
	 * Make hash from uploaded file.
	 *
	 * @param  SplFileInfo $file
	 * @param  boolean $case_sens
	 * @return string
	 */
	public function file(SplFileInfo $file, $case_sens = false)
	{
		return $this->encodeHash32(hash_file('sha256', $file->getRealPath(), true), $case_sens);
	}
}
