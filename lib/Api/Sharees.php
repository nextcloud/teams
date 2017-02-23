<?php
/**
 * Circles - bring cloud-users closer
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@pontapreta.net>
 * @copyright 2017
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Circles\Api;

use OCA\Circles\Model\Circle;
use OCP\Share;


class Sharees {


	static protected function getContainer() {
		$app = new \OCA\Circles\AppInfo\Application();

		return $app->getContainer();
	}

	public static function search($search, $limit, $offset) {
		$c = self::getContainer();

		$data = $c->query('CirclesService')
					->listCircles(Circle::CIRCLES_ALL, $search);

		foreach ($data as $entry) {
			$result['circles'][] = [
				'label' => $entry->getName(),
				'value' => [
					'shareType' => Share::SHARE_TYPE_CIRCLE,
					'shareWith' => $entry->getId()
				],
			];
		}

		return $result;
	}


}