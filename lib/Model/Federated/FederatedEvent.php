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


namespace OCA\Circles\Model\Federated;


use daita\MySmallPhpTools\Model\SimpleDataStore;
use daita\MySmallPhpTools\Traits\TArrayTools;
use JsonSerializable;
use OCA\Circles\Model\Circle;
use OCA\Circles\Model\Member;


/**
 * Class FederatedEvent
 *
 * @package OCA\Circles\Model\Federated
 */
class FederatedEvent implements JsonSerializable {


	const SEVERITY_LOW = 1;
	const SEVERITY_HIGH = 3;

	const BYPASS_LOCALCIRCLECHECK = 1;
	const BYPASS_INITIATORCHECK = 2;


	use TArrayTools;


	/** @var string */
	private $class;

	/** @var string */
	private $source = '';

	/** @var Circle */
	private $circle;

	/** @var Member */
	private $member;

	/** @var SimpleDataStore */
	private $data;

	/** @var int */
	private $severity = self::SEVERITY_LOW;

	/** @var SimpleDataStore */
	private $outcome;

	/** @var SimpleDataStore */
	private $result;

	/** @var bool */
	private $async = false;

	/** @var string */
	private $incomingOrigin = '';


	/** @var string */
	private $wrapperToken = '';

	/** @var bool */
	private $verifiedViewer = false;

	/** @var bool */
	private $verifiedCircle = false;

	/** @var int */
	private $bypass = 0;


	/**
	 * FederatedEvent constructor.
	 *
	 * @param string $class
	 */
	function __construct(string $class = '') {
		$this->class = $class;
		$this->data = new SimpleDataStore();
		$this->result = new SimpleDataStore();
		$this->outcome = new SimpleDataStore();
	}


	/**
	 * @return string
	 */
	public function getClass(): string {
		return $this->class;
	}

	/**
	 * @param mixed $class
	 *
	 * @return self
	 */
	public function setClass($class): self {
		$this->class = $class;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getSource(): string {
		return $this->source;
	}

	/**
	 * @param string $source
	 *
	 * @return self
	 */
	public function setSource(string $source): self {
		$this->source = $source;

		if ($this->hasMember() && $this->member->getInstance() === '') {
			$this->member->setInstance($source);
		}

//		if ($this->hasCircle()
//			&& $this->getCircle()
//					->hasViewer()
//			&& $this->getCircle()
//					->getViewer()
//					->getInstance() === '') {
//			$this->getCircle()
//				 ->getViewer()
//				 ->setInstance($source);
//		}

		return $this;
	}


	/**
	 * @return bool
	 */
	public function isAsync(): bool {
		return $this->async;
	}

	/**
	 * @param bool $async
	 *
	 * @return self
	 */
	public function setAsync(bool $async): self {
		$this->async = $async;

		return $this;
	}

	/**
	 * @param string $incomingOrigin
	 *
	 * @return self
	 */
	public function setIncomingOrigin(string $incomingOrigin): self {
		$this->incomingOrigin = $incomingOrigin;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getIncomingOrigin(): string {
		return $this->incomingOrigin;
	}


	/**
	 * @param bool $verifiedViewer
	 *
	 * @return FederatedEvent
	 */
	public function setVerifiedViewer(bool $verifiedViewer): self {
		$this->verifiedViewer = $verifiedViewer;

		return $this;
	}

//	/**
//	 * @return bool
//	 */
//	public function isVerifiedViewer(): bool {
//		return $this->verifiedViewer;
//	}

//	/**
//	 * @throws InitiatorNotConfirmedException
//	 */
//	public function confirmVerifiedViewer(): void {
//		if ($this->isVerifiedViewer()) {
//			return;
//		}
//
//		throw new InitiatorNotConfirmedException();
//	}


//	/**
//	 * @param bool $verifiedCircle
//	 *
//	 * @return FederatedEvent
//	 */
//	public function setVerifiedCircle(bool $verifiedCircle): self {
//		$this->verifiedCircle = $verifiedCircle;
//
//		return $this;
//	}
//
//	/**
//	 * @return bool
//	 */
//	public function isVerifiedCircle(): bool {
//		return $this->verifiedCircle;
//	}


	/**
	 * @param string $wrapperToken
	 *
	 * @return FederatedEvent
	 */
	public function setWrapperToken(string $wrapperToken): self {
		$this->wrapperToken = $wrapperToken;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getWrapperToken(): string {
		return $this->wrapperToken;
	}


	/**
	 * @return bool
	 */
	public function hasCircle(): bool {
		return ($this->circle !== null);
	}

	/**
	 * @param Circle $circle
	 *
	 * @return self
	 */
	public function setCircle(Circle $circle): self {
		$this->circle = $circle;

		return $this;
	}

	/**
	 * @return Circle
	 */
	public function getCircle(): Circle {
		return $this->circle;
	}


	/**
	 * @return Member
	 */
	public function getMember(): Member {
		return $this->member;
	}

	/**
	 * @param Member $member
	 *
	 * @return self
	 */
	public function setMember(Member $member): self {
		$this->member = $member;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasMember(): bool {
		return ($this->member !== null);
	}


	/**
	 * @param SimpleDataStore $data
	 *
	 * @return self
	 */
	public function setData(SimpleDataStore $data): self {
		$this->data = $data;

		return $this;
	}

	/**
	 * @return SimpleDataStore
	 */
	public function getData(): SimpleDataStore {
		return $this->data;
	}


	/**
	 * @return int
	 */
	public function getSeverity(): int {
		return $this->severity;
	}

	/**
	 * @param int $severity
	 *
	 * @return self
	 */
	public function setSeverity(int $severity): self {
		$this->severity = $severity;

		return $this;
	}


	/**
	 * @return SimpleDataStore
	 */
	public function getOutcome(): SimpleDataStore {
		return $this->outcome;
	}

	/**
	 * @param string $message
	 * @param array $params
	 * @param bool $success
	 *
	 * @return $this
	 */
	public function setOutcome(string $message, array $params = [], bool $success = true): self {
		$this->outcome = new SimpleDataStore(
			[
				'message' => $message,
				'params'  => $params,
				'success' => $success
			]
		);

		return $this;
	}


	/**
	 * @return SimpleDataStore
	 */
	public function getResult(): SimpleDataStore {
		return $this->result;
	}

	/**
	 * @param SimpleDataStore $result
	 *
	 * @return self
	 */
	public function setResult(SimpleDataStore $result): self {
		$this->result = $result;

		return $this;
	}


	/**
	 * @param array $data
	 *
	 * @return self
	 */
	public function import(array $data): self {
		$this->setClass($this->get('class', $data));
		$this->setSeverity($this->getInt('severity', $data));
		$this->setData(new SimpleDataStore($this->getArray('data', $data)));
		$this->setResult(new SimpleDataStore($this->getArray('result', $data)));
		$this->setSource($this->get('source', $data));
		$this->setAsync($this->getBool('async', $data));

		if (array_key_exists('circle', $data)) {
			$circle = new Circle();
			$circle->import($this->getArray('circle', $data));
			$this->setCircle($circle);
		}

		if (array_key_exists('member', $data)) {
			$member = new Member();
			$member->import($this->getArray('member', $data));
			$this->setMember($member);
		}

		return $this;
	}


	/**
	 * @return array
	 */
	function jsonSerialize(): array {
		$arr = [
			'class'    => $this->getClass(),
			'severity' => $this->getSeverity(),
			'data'     => $this->getData(),
			'result'   => $this->getResult(),
			'source'   => $this->getSource(),
			'outcome'  => $this->getOutcome(),
			'async'    => $this->isAsync()
		];

		if ($this->hasCircle()) {
			$arr['circle'] = $this->getCircle();
		}
		if ($this->hasMember()) {
			$arr['member'] = $this->getMember();
		}

		return $arr;
	}


	/**
	 * @param int $flag
	 *
	 * @return FederatedEvent
	 */
	public function bypass(int $flag): self {
		if (!$this->canBypass($flag)) {
			$this->bypass += $flag;
		}

		return $this;
	}

	/**
	 * @param int $flag
	 *
	 * @return bool
	 */
	public function canBypass(int $flag): bool {
		return (($this->bypass & $flag) !== 0);
	}

}
