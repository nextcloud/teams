<?php


namespace OCA\Circles\Events;


use OCA\Circles\Service\CirclesService;
use OCA\Circles\Service\GroupsService;
use OCA\Circles\Service\MembersService;
use OCA\Circles\Service\MiscService;
use OCP\Util;
use OC\User\User;
use OC\Log;
use OCA\Circles\AppInfo\Application;
use OCA\Circles\Model\Member;

class UserEvents {
	
	/** Default for warning log level **/
	const logLevel = 2;

	/** @var CirclesService */
	private $circlesService;

	/** @var MembersService */
	private $membersService;

	/** @var GroupsService */
	private $groupsService;

	/** @var MiscService */
	private $miscService;
	
	/** @var User */
	private static $user = null;

	/**
	 * UserEvents constructor.
	 *
	 * @param CirclesService $circlesService
	 * @param MembersService $membersService
	 * @param GroupsService $groupsService
	 * @param MiscService $miscService
	 */
	public function __construct(
		CirclesService $circlesService, MembersService $membersService, GroupsService $groupsService,
		MiscService $miscService
	) {
		$this->circlesService = $circlesService;
		$this->membersService = $membersService;
		$this->groupsService = $groupsService;
		$this->miscService = $miscService;
	}


	/**
	 * @param array $params
	 */
	public function onUserDeleted(array $params) {
		$userId = $params['uid'];
		$this->circlesService->onUserRemoved($userId);
		$this->membersService->onUserRemoved($userId);
	}


	/**
	 * @param array $params
	 */
	public function onGroupDeleted(array $params) {
		$groupId = $params['gid'];
		$this->groupsService->onGroupRemoved($groupId);
	}
	
	/**
	 * @param array $params
	 */
	public function onCircleCreated(array $params) {
		$circle = $params['circle'];
		$user = $this->getUser()->getDisplayName();
		$this->miscService->log("user $user created circle $circle", self::logLevel);
	}

	/**
	 * @param array $params
	 */
	public function onCircleDestroyed(array $params) {
		$circle = $params['circle'];
		$user = $this->getUser()->getDisplayName();
		$this->miscService->log("user $user destroyed circle $circle", self::logLevel);
	}

	/**
	 * @param array $params
	 */
	public function onCircleUpdated(array $params) {
		$formerCircle = $params['former_name'];
		$circle = $params['circle_name'];
		if ($formerCircle != $circle){
			$user = $this->getUser()->getDisplayName();
			$this->miscService->log("user $user updated circle $formerCircle to $circle", self::logLevel);
		}
	}

	/**
	 * @param array $params
	 */
	public function onMemberAdded(array $params) {
		$circle = $params['circle'];
		$member = $params['member'];
		$user = $this->getUser()->getDisplayName();
		$this->miscService->log("user $user added member $member to circle $circle", self::logLevel);
	}

	/**
	 * @param array $params
	 */
	public function onMemberRemoved(array $params) {
		$circle = $params['circle'];
		$member = $params['member'];
		$user = $this->getUser()->getDisplayName();
		$this->miscService->log("user $user removed member $member from circle $circle", self::logLevel);
	}

	/**
	 * @param array $params
	 */
	public function onItemShared(array $params) {
		$shareWith = $params['shareWith'];
		$fileTarget = $params['fileTarget'];
		$user = $this->getUser()->getDisplayName();
		$this->miscService->log("user $user shared $fileTarget with $shareWith", self::logLevel);
	}

	/**
	 * @param array $params
	 */
	public function onItemUnshared(array $params) {
		$shareWith = $params['shareWith'];
		$fileTarget = $params['fileTarget'];
		$user = $this->getUser()->getDisplayName();
		if (!empty($shareWith)){
			if (ctype_alnum($shareWith)){
				try {
					$shareWith = $this->circlesService->detailsCircle($shareWith)->getName();
				} catch (\Exception $e) {
				}
			}
			$this->miscService->log("user $user unshared $fileTarget with $shareWith", self::logLevel);
		}
	}
	
	/**
	 * @param array $params
	 */
	public function onMemberJoined(array $params) {
		$member = $params['member'];
		$circle = $params['circle'];
		$this->miscService->log("member $member joined circle $circle", self::logLevel);
	}

	/**
	 * @param array $params
	 */
	public function onMemberInvited(array $params) {
		$member = $params['member'];
		$circle = $params['circle'];
		$author = $params['author'];		
		$this->miscService->log("member $member has been invited to join circle $circle by $author", self::logLevel);
	}

	/**
	 * @param array $params
	 */
	public function onMemberLeft(array $params) {
		$member = $params['member'];
		$circle = $params['circle'];
		$this->miscService->log("member $member left circle $circle", self::logLevel);
	}

	/**
	 * Expects one of two scenarios for params:
	 * - member,circle and author (initiative from invitator)
	 * - member,circle and status (initiative from invited)
	 * @param array $params
	 */
	public function onMemberInvitationCancelled(array $params) {
		$member = $params['member'];
		$circle = $params['circle'];
		if (isset($params['author'])){
			$this->miscService->log("$author cancelled $member's invitation to join $circle", self::logLevel);
		} else if ($params['status'] == Member::STATUS_INVITED){
				$this->miscService->log("$member declined an invitation to join $circle", self::logLevel);
		} else {
			$this->miscService->log("$member cancelled his request to join $circle", self::logLevel);
		}
	}

	/**
	 * @param array $params
	 */
	public function onMemberLevelChanged(array $params) {
		$member = $params['member'];
		$circle = $params['circle'];
		$level = $params['level'];
		$levelString = Member::getLevelStringFromCode($level);
		$user = $this->getUser()->getDisplayName();
		$this->miscService->log("$user changed level of $member from circle $circle to $levelString", self::logLevel);
	}

	/**
	 * @return User
	 */
	private function getUser()
	{
		if (self::$user == null){
			$app = new Application();
			self::$user = $app->getContainer()->query('UserSession')->getUser();
		}
		return self::$user;
	}
}