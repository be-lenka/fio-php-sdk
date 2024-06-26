<?php declare(strict_types=1);

namespace Belenka\Fio\Response\Read;

use Belenka\Fio\Exceptions;
use Belenka\Fio\Utils;

/**
 * @implements \Iterator<string, mixed>
 */
abstract class TransactionAbstract implements \Iterator
{
	/** @var array<string, mixed> */
	private $properties = [];

	/** @var string */
	protected $dateFormat;


	public function __construct(string $dateFormat)
	{
		$this->dateFormat = $dateFormat;
	}


	/**
	 * @return mixed
	 */
	public function __get(string $name)
	{
		if (array_key_exists($name, $this->properties)) {
			return $this->properties[$name];
		}
		throw new Exceptions\Runtime('Property does not exists. ' . $name);
	}


	public function clearTemporaryValues(): void
	{
		$this->dateFormat = '';
	}


	/**
	 * @param mixed $value
	 */
	public function bindProperty(string $name, string $type, $value): void
	{
		$method = 'set' . ucfirst($name);
		if (method_exists($this, $method)) {
			$value = $this->{$method}($value);
		} elseif ($value !== null) {
			$value = $this->checkValue($value, $type);
		}
		$this->properties[$name] = $value;
	}


	/**
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function current()
	{
		return current($this->properties);
	}


	/**
	 * @return string
	 */
	#[\ReturnTypeWillChange]
	public function key()
	{
		$key = key($this->properties);
		if ($key === NULL) {
			throw new Exceptions\InvalidState('Key cold\'nt be null.');
		}
		return $key;
	}


	#[\ReturnTypeWillChange]
	public function next()
	{
		next($this->properties);
	}


	#[\ReturnTypeWillChange]
	public function rewind()
	{
		reset($this->properties);
	}


	#[\ReturnTypeWillChange]
	public function valid()
	{
		$key = key($this->properties);
		if ($key === null) {
			return false;
		}

		return array_key_exists($key, $this->properties);
	}


	/** @return array<string, mixed> */
	public function getProperties(): array
	{
		return $this->properties;
	}


	/**
	 * @param mixed $value
	 * @return mixed
	 */
	protected function checkValue($value, string $type)
	{
		switch ($type) {
			case 'datetime':
				return Utils\Strings::createFromFormat($value, $this->dateFormat);
			case 'float':
				return floatval($value);
			case 'string':
				return trim($value);
			case 'int':
				return intval($value);
			case 'string|null':
				return trim($value) ?: null;
		}
		return $value;
	}

}
