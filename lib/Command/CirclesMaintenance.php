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


namespace OCA\Circles\Command;


use OC\Core\Command\Base;
use OCA\Circles\Db\CoreRequestBuilder;
use OCA\Circles\Service\MaintenanceService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;


/**
 * Class CirclesMaintenance
 *
 * @package OCA\Circles\Command
 */
class CirclesMaintenance extends Base {


	/** @var CoreRequestBuilder */
	private $coreQueryBuilder;

	/** @var MaintenanceService */
	private $maintenanceService;


	/**
	 * CirclesMaintenance constructor.
	 *
	 * @param CoreRequestBuilder $coreQueryBuilder
	 * @param MaintenanceService $maintenanceService
	 */
	public function __construct(
		CoreRequestBuilder $coreQueryBuilder,
		MaintenanceService $maintenanceService
	) {
		parent::__construct();
		$this->coreQueryBuilder = $coreQueryBuilder;
		$this->maintenanceService = $maintenanceService;
	}


	protected function configure() {
		parent::configure();
		$this->setName('circles:maintenance')
			 ->setDescription('Clean stuff, keeps the app running')
			 ->addOption('level', '', InputOption::VALUE_REQUIRED, 'level of maintenance', '0')
			 ->addOption(
				 'reset', '', InputOption::VALUE_NONE, 'reset Circles; remove all data related to the App'
			 )
			 ->addOption(
				 'uninstall', '', InputOption::VALUE_NONE,
				 'Uninstall the apps and everything related to the app from the database'
			 );
	}


	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$reset = $input->getOption('reset');
		$uninstall = $input->getOption('uninstall');
		$level = (int)$input->getOption('level');

		if ($reset || $uninstall) {
			$action = strtolower(($uninstall) ? 'uninstall' : 'reset');

			$output->writeln('');
			$output->writeln('');
			$output->writeln(
				'<error>WARNING! You are about to delete all data related to the Circles App!</error>'
			);
			$question = new ConfirmationQuestion(
				'<comment>Do you really want to ' . $action . ' Circles ?</comment> (y/N) ', false, '/^(y|Y)/i'
			);

			$helper = $this->getHelper('question');
			if (!$helper->ask($input, $output, $question)) {
				$output->writeln('aborted.');

				return 0;
			}

			$output->writeln('');
			$output->writeln('<error>WARNING! This operation is not reversible.</error>');


			$question = new Question(
				'<comment>Please confirm this destructive operation by typing \'' . $action
				. '\'</comment>: ', ''
			);

			$helper = $this->getHelper('question');
			$confirmation = $helper->ask($input, $output, $question);
			if (strtolower($confirmation) !== $action) {
				$output->writeln('aborted.');

				return 0;
			}

			$this->coreQueryBuilder->cleanDatabase();
			if ($uninstall) {
				$this->coreQueryBuilder->uninstall();
			}

			$output->writeln('<info>' . $action . ' done</info>');

			return 0;
		}

		$this->maintenanceService->setOccOutput($output);
		$this->maintenanceService->runMaintenance($level);

		$output->writeln('');
		$output->writeln('<info>done</info>');

		return 0;
	}

}


