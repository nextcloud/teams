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


namespace OCA\Circles\Service;


use daita\MySmallPhpTools\Traits\TArrayTools;
use daita\MySmallPhpTools\Traits\TStringTools;
use OCA\Circles\Db\RemoteRequest;
use OCA\Circles\Exceptions\RemoteNotFoundException;
use OCA\Circles\Exceptions\UnknownInterfaceException;
use OCP\IRequest;
use OCP\IURLGenerator;


/**
 * Class InterfaceService
 *
 * @package OCA\Circles\Service
 */
class InterfaceService {

	const IFACE0 = 1;
	const IFACE1 = 2;
	const IFACE2 = 3;
	const IFACE3 = 4;
	const IFACE4 = 5;
	const IFACE_INTERNAL = 6;
	const IFACE_FRONTAL = 7;

	public static $LIST_IFACE = [
		self::IFACE_INTERNAL => 'internal',
		self::IFACE_FRONTAL  => 'frontal',
		self::IFACE0         => 'iface0',
		self::IFACE1         => 'iface1',
		self::IFACE2         => 'iface2',
		self::IFACE3         => 'iface3',
		self::IFACE4         => 'iface4',
	];


	use TStringTools;
	use TArrayTools;


	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var RemoteRequest */
	private $remoteRequest;

	/** @var ConfigService */
	private $configService;


	/** @var int */
	private $currentInterface = 0;

	/** @var int */
	private $outgoingInterface = 0;


	/**
	 * InterfaceService constructor.
	 *
	 * @param IURLGenerator $urlGenerator
	 * @param RemoteRequest $remoteRequest
	 * @param ConfigService $configService
	 */
	public function __construct(
		IURLGenerator $urlGenerator,
		RemoteRequest $remoteRequest,
		ConfigService $configService
	) {
		$this->urlGenerator = $urlGenerator;
		$this->remoteRequest = $remoteRequest;
		$this->configService = $configService;
	}


	/**
	 * @param int $interface
	 */
	public function setCurrentInterface(int $interface): void {
		$this->currentInterface = $interface;
	}

	/**
	 * @return int
	 * @throws UnknownInterfaceException
	 */
	public function getCurrentInterface(): int {
		if ($this->currentInterface === 0) {
			throw new UnknownInterfaceException('interface not initialized');
		}

		return $this->currentInterface;
	}

	/**
	 * @return bool
	 */
	public function hasCurrentInterface(): bool {
		return ($this->currentInterface !== 0);
	}


	/**
	 * @param IRequest $request
	 */
	public function setCurrentInterfaceFromRequest(IRequest $request): void {
		$testing = [
			self::IFACE_INTERNAL => $this->configService->getInternalInstance(),
			self::IFACE_FRONTAL  => $this->configService->getFrontalInstance(),
			self::IFACE0         => $this->configService->getIfaceInstance(self::IFACE0),
			self::IFACE1         => $this->configService->getIfaceInstance(self::IFACE1),
			self::IFACE2         => $this->configService->getIfaceInstance(self::IFACE2),
			self::IFACE3         => $this->configService->getIfaceInstance(self::IFACE3),
			self::IFACE4         => $this->configService->getIfaceInstance(self::IFACE4),
		];

		$serverHost = strtolower($request->getServerHost());
		if ($serverHost === '') {
			return;
		}

		foreach ($testing as $iface => $instance) {
			if ($serverHost === strtolower($instance)) {
				$this->setCurrentInterface($iface);

				return;
			}
		}
	}


	/**
	 *
	 */
	public function setCurrentInterfaceFromInstance(string $instance): void {
		try {
			$remoteInstance = $this->remoteRequest->getFromInstance($instance);
			$this->setCurrentInterface($remoteInstance->getInterface());
		} catch (RemoteNotFoundException $e) {
		}
	}


	/**
	 * use this only if interface must be defined. If not, use getLocalInstance()
	 *
	 * @throws UnknownInterfaceException
	 */
	public function getCloudInstance(): string {
		switch ($this->getCurrentInterface()) {
			case self::IFACE_INTERNAL:
				return $this->configService->getInternalInstance();
			case self::IFACE_FRONTAL:
				return $this->configService->getFrontalInstance();
			case self::IFACE0:
			case self::IFACE1:
			case self::IFACE2:
			case self::IFACE3:
			case self::IFACE4:
				return $this->configService->getIfaceInstance($this->getCurrentInterface());
		}

		throw new UnknownInterfaceException('unknown configured interface');
	}


	/**
	 * @throws UnknownInterfaceException
	 */
	public function getCloudPath(string $route = '', array $args = []): string {
		$scheme = '';
		switch ($this->getCurrentInterface()) {
			case self::IFACE_INTERNAL:
				$scheme = $this->configService->getAppValue(ConfigService::INTERNAL_CLOUD_SCHEME);
				break;
			case self::IFACE_FRONTAL:
				$scheme = $this->configService->getAppValue(ConfigService::FRONTAL_CLOUD_SCHEME);
				break;
			case self::IFACE0:
				$scheme = $this->configService->getAppValue(ConfigService::IFACE0_CLOUD_SCHEME);
				break;
			case self::IFACE1:
				$scheme = $this->configService->getAppValue(ConfigService::IFACE1_CLOUD_SCHEME);
				break;
			case self::IFACE2:
				$scheme = $this->configService->getAppValue(ConfigService::IFACE2_CLOUD_SCHEME);
				break;
			case self::IFACE3:
				$scheme = $this->configService->getAppValue(ConfigService::IFACE3_CLOUD_SCHEME);
				break;
			case self::IFACE4:
				$scheme = $this->configService->getAppValue(ConfigService::IFACE4_CLOUD_SCHEME);
				break;
		}

		if ($scheme === '') {
			throw new UnknownInterfaceException('misconfigured scheme');
		}

		$base = $scheme . '://' . $this->getCloudInstance();

		if ($route === '') {
			return $base;
		}

		return $base . $this->urlGenerator->linkToRoute($route, $args);
	}


	/**
	 * should be used when unsure about the used Interface
	 *
	 * @return string
	 */
	public function getLocalInstance(): string {
		if ($this->hasCurrentInterface()) {
			try {
				return $this->getCloudInstance();
			} catch (UnknownInterfaceException $e) {
			}
		}

		if ($this->configService->getFrontalInstance() !== '') {
			return $this->configService->getFrontalInstance();
		}

		if ($this->configService->getInternalInstance() !== '') {
			return $this->configService->getInternalInstance();
		}

		return $this->configService->getLoopbackInstance();
	}

}
