<?php namespace Ideil\GenericFile\Traits;

trait HashingTrait {

	/**
	 * Convert number to selected base
	 *
	 * @param  integer $number
	 * @param  integer $base
	 * @return char
	 */
	protected function toBase($num, $b)
	{
		$base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$r = $num % $b ;
		$res = $base[$r];
		$q = floor($num/$b);

		while ($q) {
			$r = $q % $b;
			$q = floor($q/$b);
			$res = $base[$r].$res;
		}

		return $res;
	}

	/**
	 * Convert hashed data to base32
	 *
	 * @param  string $hash
	 * @param  boolean $case_sens
	 * @return string
	 */
	protected function encodeHash32($hash, $case_sens = true)
	{
		$code = '';

		for ($i = 0; $i < strlen($hash) / 2; $i++)
		{
			$code .= $this->toBase((ord($hash[$i * 2]) << 8) + ord($hash[$i * 2 + 1]), $case_sens ? 62 : 32);
		}

		return substr($code, 0, 32);
	}

	/**
	 * Make hash from string.
	 *
	 * @param  string $str
	 * @param  boolean $case_sens
	 * @return string
	 */
	public function str($str, $case_sens = true)
	{
		return $this->encodeHash32(hash('sha256', $str, true), $case_sens);
	}
}
