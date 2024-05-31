<?php declare(strict_types=1);

namespace Belenka\Fio\Request\Pay\Payment;

use Belenka\Fio\Exceptions\InvalidArgument;

trait Symbols
{
	/** @var int */
	protected $ks = 0;

	/** @var int */
	protected $vs = 0;

	/** @var int */
	protected $ss = 0;


	/**
	 * @return static
	 */
	public function setConstantSymbol(int $ks)
	{
		$this->ks = InvalidArgument::checkRange($ks, 9999);
		return $this;
	}


	/**
	 * @return static
	 */
	public function setVariableSymbol(int $vs)
	{
		$this->vs = InvalidArgument::checkRange($vs, 9999999999);
		return $this;
	}


	/**
	 * @return static
	 */
	public function setSpecificSymbol(int $ss)
	{
		$this->ss = InvalidArgument::checkRange($ss, 9999999999);
		return $this;
	}

}
