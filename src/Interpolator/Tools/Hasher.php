<?php namespace Ideil\GenericFile\Interpolator\Tools;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Hasher {

	use \Ideil\GenericFile\Traits\HashingTrait;

	/**
	 * Make hash from uploaded file.
	 *
	 * @param  Symfony\Component\HttpFoundation\File\UploadedFile $file
	 * @param  boolean $case_sens
	 * @return string
	 */
	public function file(UploadedFile $file, $case_sens = false)
	{
		return $this->encodeHash32(hash_file('sha256', $file->getRealPath(), true), $case_sens);
	}
}
