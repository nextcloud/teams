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


namespace OCA\Circles\FederatedItems;


use daita\MySmallPhpTools\Traits\Nextcloud\nc22\TNC22Logger;
use daita\MySmallPhpTools\Traits\TStringTools;
use Exception;
use OCA\Circles\Db\MemberRequest;
use OCA\Circles\Exceptions\FederatedItemBadRequestException;
use OCA\Circles\Exceptions\FederatedUserException;
use OCA\Circles\Exceptions\InvalidIdException;
use OCA\Circles\Exceptions\MemberAlreadyExistsException;
use OCA\Circles\Exceptions\MemberNotFoundException;
use OCA\Circles\Exceptions\MembersLimitException;
use OCA\Circles\IFederatedItem;
use OCA\Circles\IFederatedItemInitiatorMembershipNotRequired;
use OCA\Circles\IFederatedItemMemberCheckNotRequired;
use OCA\Circles\IFederatedItemMemberOptional;
use OCA\Circles\Model\Circle;
use OCA\Circles\Model\Federated\FederatedEvent;
use OCA\Circles\Model\FederatedUser;
use OCA\Circles\Model\ManagedModel;
use OCA\Circles\Model\Member;
use OCA\Circles\Service\CircleService;
use OCA\Circles\Service\ConfigService;
use OCA\Circles\Service\EventService;
use OCA\Circles\Service\FederatedUserService;
use OCA\Circles\StatusCode;
use OCP\IUserManager;


/**
 * Class CircleJoin
 *
 * @package OCA\Circles\GlobalScale
 */
class CircleJoin implements
	IFederatedItem,
	IFederatedItemInitiatorMembershipNotRequired,
//	IFederatedItemAsyncProcess,
	IFederatedItemMemberCheckNotRequired,
	IFederatedItemMemberOptional {


	use TStringTools;
	use TNC22Logger;


	/** @var IUserManager */
	private $userManager;

	/** @var MemberRequest */
	private $memberRequest;

	/** @var FederatedUserService */
	private $federatedUserService;

	/** @var CircleService */
	private $circleService;

	/** @var EventService */
	private $eventService;

	/** @var ConfigService */
	private $configService;


	/**
	 * CircleJoin constructor.
	 *
	 * @param IUserManager $userManager
	 * @param MemberRequest $memberRequest
	 * @param FederatedUserService $federatedUserService
	 * @param CircleService $circleService
	 * @param EventService $eventService
	 * @param ConfigService $configService
	 */
	public function __construct(
		IUserManager $userManager, MemberRequest $memberRequest, FederatedUserService $federatedUserService,
		CircleService $circleService, EventService $eventService, ConfigService $configService
	) {
		$this->userManager = $userManager;
		$this->memberRequest = $memberRequest;
		$this->federatedUserService = $federatedUserService;
		$this->circleService = $circleService;
		$this->eventService = $eventService;
		$this->configService = $configService;
	}


	/**
	 * @param FederatedEvent $event
	 *
	 * @throws FederatedItemBadRequestException
	 * @throws MembersLimitException
	 */
	public function verify(FederatedEvent $event): void {
		$circle = $event->getCircle();
		$initiator = $circle->getInitiator();

//		$initiatorHelper = new MemberHelper($initiator);
//		$initiatorHelper->cannotBeMember();

		$member = new Member();
		$member->importFromIFederatedUser($initiator);
		$member->setCircleId($circle->getId());
		$this->manageMemberStatus($circle, $member);

		$this->circleService->confirmCircleNotFull($circle);

		$event->setMember($member)
			  ->setOutcome($member->jsonSerialize());

		// TODO: Managing cached name
		//		$member->setCachedName($eventMember->getCachedName());

		return;

//
//
//		$federatedId = $member->getUserId() . '@' . $member->getInstance();
//		try {
//			$federatedUser =
//				$this->federatedUserService->getFederatedUser($federatedId, $member->getUserType());
//			throw new MemberNotFoundException(
//				ucfirst(Member::$DEF_TYPE[$member->getUserType()]) . ' \'%s\' not found',
//				['member' => $member->getUserId() . '@' . $member->getInstance()]
//			);
//		}

//		$member->importFromIFederatedUser($federatedUser);
//
//		try {
//			$knownMember = $this->memberRequest->searchMember($member);
//			// TODO: maybe member is requesting access
//			throw new MemberAlreadyExistsException(
//				ucfirst(Member::$DEF_TYPE[$member->getUserType()]) . ' %s is already a member',
//				['member' => $member->getUserId() . '@' . $member->getInstance()]
//			);
//		} catch (MemberNotFoundException $e) {
//		}

//		$member->setId($this->uuid(ManagedModel::ID_LENGTH));
//
//		// TODO: check Config on Circle to know if we set Level to 1 or just send an invitation
//		$member->setLevel(Member::LEVEL_MEMBER);
//		$member->setStatus(Member::STATUS_MEMBER);
//		$event->setDataOutcome(['member' => $member]);
//
//		// TODO: Managing cached name
//		//		$member->setCachedName($eventMember->getCachedName());
//		$this->circleService->confirmCircleNotFull($circle);
//
//		// TODO: check if it is a member or a mail or a circle and fix the returned message
//
//		return;


//		$member = $this->membersRequest->getFreshNewMember(
//			$circle->getUniqueId(), $ident, $eventMember->getType(), $eventMember->getInstance()
//		);
//		$member->hasToBeInviteAble()
//
//		$this->membersService->addMemberBasedOnItsType($circle, $member);
//
//		$password = '';
//		$sendPasswordByMail = false;
//		if ($this->configService->enforcePasswordProtection($circle)) {
//			if ($circle->getSetting('password_single_enabled') === 'true') {
//				$password = $circle->getPasswordSingle();
//			} else {
//				$sendPasswordByMail = true;
//				$password = $this->miscService->token(15);
//			}
//		}
//
//		$event->setData(
//			new SimpleDataStore(
//				[
//					'password'       => $password,
//					'passwordByMail' => $sendPasswordByMail
//				]
//			)
//		);
	}


	/**
	 * @param FederatedEvent $event
	 *
	 * @throws InvalidIdException
	 */
	public function manage(FederatedEvent $event): void {
		$member = $event->getMember();

		try {
			$this->memberRequest->getMember($member->getId());

			return;
		} catch (MemberNotFoundException $e) {
		}

		try {
			$federatedUser = new FederatedUser();
			$federatedUser->importFromIFederatedUser($member);
			$this->federatedUserService->confirmLocalSingleId($federatedUser);
		} catch (FederatedUserException $e) {
			$this->e($e, ['member' => $member]);

			return;
		}

		$this->memberRequest->save($member);

		$this->eventService->memberJoining($event);
	}


	/**
	 * @param FederatedEvent $event
	 * @param array $results
	 */
	public function result(FederatedEvent $event, array $results): void {
		$this->eventService->memberJoined($event, $results);
	}


	/**
	 * @param Circle $circle
	 * @param Member $member
	 *
	 * @throws FederatedItemBadRequestException
	 */
	private function manageMemberStatus(Circle $circle, Member $member) {
		try {

			$knownMember = $this->memberRequest->searchMember($member);
			if ($knownMember->getLEvel() === Member::LEVEL_NONE) {
				switch ($knownMember->getStatus()) {

					case Member::STATUS_BLOCKED:
						throw new Exception('TODOTODOTODO');

					case Member::STATUS_REQUEST:
						throw new MemberAlreadyExistsException(StatusCode::$CIRCLE_JOIN[123], 123);

					case Member::STATUS_INVITED:
						$member->setLevel(Member::LEVEL_MEMBER);
						$member->setStatus(Member::STATUS_MEMBER);

						return;
				}
			}

			throw new MemberAlreadyExistsException(StatusCode::$CIRCLE_JOIN[122], 122);
		} catch (MemberNotFoundException $e) {

			if (!$circle->isConfig(Circle::CFG_OPEN)) {
				throw new Exception('TODO TODO TODO - circle not open, cannot join!');
			}

			$member->setId($this->uuid(ManagedModel::ID_LENGTH));

			if ($circle->isConfig(Circle::CFG_REQUEST)) {
				$member->setStatus(Member::STATUS_REQUEST);
			} else {
				$member->setLevel(Member::LEVEL_MEMBER);
				$member->setStatus(Member::STATUS_MEMBER);
			}
		}
	}

}
