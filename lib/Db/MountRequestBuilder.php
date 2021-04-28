<?php

declare(strict_types=1);


/**
 * Circles - Bring cloud-users closer together.
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
 * @copyright 2021
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


namespace OCA\Circles\Db;


use daita\MySmallPhpTools\Exceptions\RowNotFoundException;
use OCA\Circles\Exceptions\MountNotFoundException;
use OCA\Circles\Model\Mount;


/**
 * Class MountRequestBuilder
 *
 * @package OCA\Circles\Db
 */
class MountRequestBuilder extends CoreQueryBuilder {


	/**
	 * @return CoreRequestBuilder
	 */
	protected function getMountInsertSql(): CoreRequestBuilder {
		$qb = $this->getQueryBuilder();
		$qb->insert(self::TABLE_MOUNT);

		return $qb;
	}


	/**
	 * @return CoreRequestBuilder
	 */
	protected function getMountUpdateSql(): CoreRequestBuilder {
		$qb = $this->getQueryBuilder();
		$qb->update(self::TABLE_MOUNT);

		return $qb;
	}


	/**
	 * @param string $alias
	 *
	 * @return CoreRequestBuilder
	 */
	protected function getMountSelectSql(string $alias = CoreRequestBuilder::MOUNT): CoreRequestBuilder {
		$qb = $this->getQueryBuilder();

		$qb->select(
			$alias . '.id',
			$alias . '.mount_id',
			$alias . '.circle_id',
			$alias . '.single_id',
			$alias . '.token',
			$alias . '.parent',
			$alias . '.mountpoint',
			$alias . '.mountpoint_hash'
		)
		   ->from(self::TABLE_MOUNT, $alias)
		   ->setDefaultSelectAlias($alias);

		return $qb;
	}


	/**
	 * @return CoreRequestBuilder
	 */
	protected function getMountDeleteSql(): CoreRequestBuilder {
		$qb = $this->getQueryBuilder();
		$qb->delete(self::TABLE_MOUNT);

		return $qb;
	}


	/**
	 * @param CoreRequestBuilder $qb
	 *
	 * @return Mount
	 * @throws MountNotFoundException
	 */
	public function getItemFromRequest(CoreRequestBuilder $qb): Mount {
		/** @var Mount $circle */
		try {
			$circle = $qb->asItem(
				Mount::class,
				[
					'local' => $this->configService->getFrontalInstance()
				]
			);
		} catch (RowNotFoundException $e) {
			throw new MountNotFoundException('Mount not found');
		}

		return $circle;
	}

	/**
	 * @param CoreRequestBuilder $qb
	 *
	 * @return Mount[]
	 */
	public function getItemsFromRequest(CoreRequestBuilder $qb): array {
		/** @var Mount[] $result */
		return $qb->asItems(
			Mount::class,
			[
				// TODO: we might need a getInstance() based on a frontal/internal request ?
				// TODO: as on some setup, there 2 ways of defining the local instance (GS+Federated)
				'local' => $this->configService->getFrontalInstance()
			]
		);
	}

}

