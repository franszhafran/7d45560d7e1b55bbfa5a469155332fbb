<?php declare(strict_types=1);

namespace Forrest79\PhPgSql\Db;

interface RowFactory
{

	/**
	 * @param array<string, mixed> $rawValues
	 */
	function createRow(ColumnValueParser $columnValueParser, array $rawValues): Row;

}
