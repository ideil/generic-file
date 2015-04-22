<?php namespace Ideil\GenericFile\Interpolator;

class HandlersBase extends Handlers {

	/**
	 * Return default handlers
	 *
	 * @return array
	 */
	public function getDeafultHandlers()
	{
		return [

			// return hash of input file

			'contenthash' => function ($file) {

				if ( ! isset($this->hasher))
				{
					$this->hasher = new Tools\Hasher;
				}

				return $this->hasher->file($file);
			},

			// return ext of input file

			'ext' => function ($file) {

				return $file->getClientOriginalExtension();
			}

		];
	}

}
