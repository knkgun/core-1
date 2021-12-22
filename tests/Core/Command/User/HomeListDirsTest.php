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

namespace Tests\Core\Command\User;

use OC\Core\Command\User\HomeListDirs;
use OCP\App\IAppManager;
use OCP\IConfig;
use OCP\IUser;
use OCP\IUserManager;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

/**
 * Class HomeListDirsTest
 */
class HomeListDirsTest extends TestCase {
	/** @var CommandTester */
	private $commandTester;

	/** @var IUserManager | \PHPUnit\Framework\MockObject\MockObject */
	private $userManager;

	/** @var IConfig | \PHPUnit\Framework\MockObject\MockObject */
	protected $config;

	/** @var IAppManager | \PHPUnit\Framework\MockObject\MockObject */
	protected $appManager;

	protected function setUp(): void {
		parent::setUp();

		$this->userManager = $this->createMock(IUserManager::class);
		$this->config = $this->createMock(IConfig::class);
		$this->appManager = $this->createMock(IAppManager::class);

		$command = new HomeListDirs($this->userManager, $this->config, $this->appManager);
		$this->commandTester = new CommandTester($command);
	}

	public function testCommandInput() {
		$homePath = '/path/to/homes';
		$user1Mock = $this->createMock(IUser::class);
		$user1Mock->method('getHome')->willReturn("$homePath/user1");
		$user2Mock = $this->createMock(IUser::class);
		$user2Mock->method('getHome')->willReturn("$homePath/user2");

		$this->userManager->method('search')->willReturn([$user1Mock, $user2Mock]);
		$this->commandTester->execute([]);
		$output = $this->commandTester->getDisplay();
		$this->assertStringContainsString($homePath, $output);
	}

	public function testCommandOnPrimaryObjectStorage() {
		$this->config->method('getSystemValue')->willReturn(['objectstorage']);
		$this->appManager->method('isEnabledForUser')->willReturn(true);
		$this->commandTester->execute([]);
		$output = $this->commandTester->getDisplay();
		$this->assertStringContainsString('This command is not supported on a primary object storage', $output);
	}
}
