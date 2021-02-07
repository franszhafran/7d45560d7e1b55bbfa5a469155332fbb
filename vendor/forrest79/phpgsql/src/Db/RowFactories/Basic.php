<?php declare(strict_types=1);

namespace Forrest79\PhPgSql\Db\RowFactories;

use Forrest79\PhPgSql\Db;

class Basic implements Db\RowFactory
{

	/**
	 * @param array<string, mixed> $rawValues
	 */
	public function createRow(Db\ColumnValueParser $columnValueParser, array $rawValues): Db\Row
	{
		return new Db\Row($columnValueParser, $rawValues);
	}

}
