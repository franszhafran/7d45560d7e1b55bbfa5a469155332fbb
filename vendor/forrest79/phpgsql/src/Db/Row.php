<?php declare(strict_types=1);

namespace Forrest79\PhPgSql\Db;

/**
 * @implements \ArrayAccess<string, mixed>
 * @implements \IteratorAggregate<string, mixed>
 */
class Row implements \ArrayAccess, \IteratorAggregate, \Countable, \JsonSerializable
{
	/** @var ColumnValueParser */
	private $columnValueParser;

	/** @var array<string, string|NULL> */
	private $rawValues;

	/** @var array<string, mixed> */
	private $values;


	/**
	 * @param array<string, mixed> $rawValues
	 */
	public function __construct(ColumnValueParser $columnValueParser, array $rawValues)
	{
		$this->columnValueParser = $columnValueParser;
		$this->rawValues = $rawValues;

		$this->values = \array_fill_keys(\array_keys($rawValues), NULL);
	}


	/**
	 * @return mixed
	 * @throws Exceptions\RowException
	 */
	public function __get(string $column)
	{
		return $this->getValue($column);
	}


	/**
	 * @param mixed $value
	 */
	public function __set(string $column, $value): void
	{
		$this->setValue($column, $value);
	}


	public function __isset(string $column): bool
	{
		return $this->hasColumn($column) && ($this->getValue($column) !== NULL);
	}


	public function __unset(string $column): void
	{
		$this->removeValue($column);
	}


	/**
	 * @return array<string, mixed>
	 */
	public function toArray(): array
	{
		// intentionally not using array_keys($this->rawValues) as $column - this is 2x faster
		foreach ($this->rawValues as $column => $value) {
			$this->parseValue($column);
		}
		return $this->values;
	}


	public function count(): int
	{
		return \count($this->values);
	}


	/**
	 * @return \ArrayIterator<string, mixed>
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->toArray());
	}


	/**
	 * @param mixed $column
	 * @return mixed
	 * @throws Exceptions\RowException
	 */
	public function offsetGet($column)
	{
		if (!\is_string($column)) {
			throw Exceptions\RowException::notStringKey();
		}
		return $this->getValue($column);
	}


	/**
	 * @param mixed $column
	 * @param mixed $value
	 */
	public function offsetSet($column, $value): void
	{
		if (!\is_string($column)) {
			throw Exceptions\RowException::notStringKey();
		}
		$this->setValue($column, $value);
	}


	/**
	 * @param mixed $column
	 * @return bool
	 */
	public function offsetExists($column): bool
	{
		if (!\is_string($column)) {
			throw Exceptions\RowException::notStringKey();
		}
		return $this->hasColumn($column) && ($this->getValue($column) !== NULL);
	}


	/**
	 * @param mixed $column
	 */
	public function offsetUnset($column): void
	{
		if (!\is_string($column)) {
			throw Exceptions\RowException::notStringKey();
		}
		$this->removeValue($column);
	}


	/**
	 * @return array<string>
	 */
	public function getColumns(): array
	{
		return \array_keys($this->values);
	}


	public function hasColumn(string $column): bool
	{
		return \array_key_exists($column, $this->values);
	}


	/**
	 * @return mixed
	 * @throws Exceptions\RowException
	 */
	private function getValue(string $column)
	{
		if (!\array_key_exists($column, $this->values)) {
			throw Exceptions\RowException::noColumn($column);
		}

		if (\array_key_exists($column, $this->rawValues)) {
			$this->parseValue($column);
		}

		return $this->values[$column];
	}


	/**
	 * @return array<string, mixed>
	 */
	public function __serialize(): array
	{
		return $this->toArray();
	}


	/**
	 * @param array<string, mixed> $values
	 */
	public function __unserialize(array $values): void
	{
		$this->resultColumns = new DummyColumnValueParser();
		$this->rawValues = [];
		$this->values = $values;
	}


	private function parseValue(string $column): void
	{
		$this->values[$column] = $this->columnValueParser->parseColumnValue($column, $this->rawValues[$column]);
		unset($this->rawValues[$column]);
	}


	/**
	 * @param mixed $value
	 * @return void
	 */
	private function setValue(string $column, $value): void
	{
		$this->values[$column] = $value;
		unset($this->rawValues[$column]);
	}


	private function removeValue(string $column): void
	{
		unset($this->rawValues[$column]);
		unset($this->values[$column]);
	}


	/**
	 * @return array<string, mixed>
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}


	/**
	 * @param array<string, mixed> $values
	 * @return static
	 */
	public static function from(array $values): self
	{
		return new static(new DummyColumnValueParser(), $values);
	}

}
