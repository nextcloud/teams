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


namespace OCA\Circles\Model\Probes;


use OCA\Circles\Model\Member;


/**
 * Class CircleProbe
 *
 * @package OCA\Circles\Model\Probes
 */
class MemberProbe extends BasicProbe {


	/** @var int */
	private $minimumLevel = 0;

	/** @var bool */
	private $canBeVisitor = false;


	/**
	 * @return $this
	 */
	public function canBeVisitor(): self {
		$this->canBeVisitor = true;
	}

	/**
	 * @return bool
	 */
	public function isViewableAsVisitor(): bool {
		return $this->canBeVisitor;
	}


	/**
	 * @return int
	 */
	public function getMinimumLevel(): int {
		return $this->minimumLevel;
	}

	/**
	 * @return $this
	 */
	public function mustBeMember(): self {
		$this->minimumLevel = Member::LEVEL_MEMBER;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function mustBeModerator(): self {
		$this->minimumLevel = Member::LEVEL_MODERATOR;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function mustBeAdmin(): self {
		$this->minimumLevel = Member::LEVEL_ADMIN;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function mustBeOwner(): self {
		$this->minimumLevel = Member::LEVEL_OWNER;

		return $this;
	}


	/**
	 * @return array
	 */
	public function getAsOptions(): array {
		return array_merge(
			[
				'minimumLevel' => $this->getMinimumLevel(),
				'viewableAsVisitor' => $this->isViewableAsVisitor()
			],
			parent::getAsOptions()
		);
	}


	/**
	 * @return array
	 */
	public function JsonSerialize(): array {
		return $this->getAsOptions();
	}
}
