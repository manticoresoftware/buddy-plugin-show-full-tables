<?php declare(strict_types=1);

/*
  Copyright (c) 2022, Manticore Software LTD (https://manticoresearch.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 2 or any later
  version. You should have received a copy of the GPL license along with this
  program; if you did not, you can find it at http://www.gnu.org/
*/

namespace Manticoresearch\Buddy\Plugin\ShowFullTables;

use Manticoresearch\Buddy\Core\Error\QueryParseError;
use Manticoresearch\Buddy\Core\Network\Request;
use Manticoresearch\Buddy\Core\Plugin\BasePayload;

/**
 * Request for Backup command that has parsed parameters from SQL
 */
final class Payload extends BasePayload {
	/**
	 * @var string $database Manticore single database with no name
	 *  so it does not matter but for future usage maybe we also parse it
	 */
	public string $database = 'Manticore';

	/**
	 * @var string $like
	 * 	It contains match pattern from LIKE statement if its presented
	 */
	public string $like = '';

	public string $path;
	public bool $hasCliEndpoint;

	/**
	 * @param Request $request
	 * @return static
	 */
	public static function fromRequest(Request $request): static {
		$pattern = '#^'
			. 'show full tables'
			. '(\s+from\s+`?(?P<database>([a-z][a-z0-9\_]*))`?)?'
			. '(\s+like\s+\'(?P<like>([^\']+))\')?'
			. '$#ius';

		if (!preg_match($pattern, $request->payload, $m)) {
			throw QueryParseError::create('You have an error in your query. Please, double-check it.');
		}

		$self = new static();
		if ($m['database'] ?? '') {
			$self->database = $m['database'];
		}
		if ($m['like'] ?? '') {
			$self->like = $m['like'];
		}
		[$self->path, $self->hasCliEndpoint] = self::getEndpointInfo($request);
		return $self;
	}

	/**
	 * @param Request $request
	 * @return bool
	 */
	public static function hasMatch(Request $request): bool {
		return stripos($request->payload, 'show full tables') === 0;
	}
}
