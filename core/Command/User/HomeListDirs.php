<?php
/**
 * @author Jannik Stehle <jstehle@owncloud.com>
 *
 * @copyright Copyright (c) 2021, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OC\Core\Command\User;

use OC\Core\Command\Base;
use OCP\App\IAppManager;
use OCP\IConfig;
use OCP\IUserManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HomeListDirs extends Base {
	/** @var \OCP\IUserManager */
	protected $userManager;

	/** @var IConfig */
	protected $config;

	/** @var IAppManager */
	protected $appManager;

	/**
	 * @param IUserManager $userManager
	 * @param IConfig $config
	 * @param IAppManager $appManager
	 */
	public function __construct(IUserManager $userManager, IConfig $config, IAppManager $appManager) {
		parent::__construct();
		$this->userManager = $userManager;
		$this->config = $config;
		$this->appManager = $appManager;
	}

	protected function configure() {
		parent::configure();

		$this
			->setName('user:home:list-dirs')
			->setDescription('List all available root directories for user homes that are currently in use');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$objectStorageAppEnabled = $this->appManager->isEnabledForUser('files_primary_s3');
		$objectStorage = $this->config->getSystemValue('objectstore', null);
		if ($objectStorageAppEnabled && $objectStorage !== null) {
			$output->writeln('<error>This command is not supported on a primary object storage</error>');
			return 1;
		}

		$users = $this->userManager->search(null);
		$homePaths = [];
		foreach ($users as $user) {
			$home = $user->getHome();
			// Strip away the UID at the end of the path
			$strippedHome = substr($home, 0, strrpos($home, '/'));
			if (!\in_array($strippedHome, $homePaths)) {
				$homePaths[] = $strippedHome;
			}
		}

		parent::writeArrayInOutputFormat($input, $output, \array_unique($homePaths), self::DEFAULT_OUTPUT_PREFIX);
		return 0;
	}
}
