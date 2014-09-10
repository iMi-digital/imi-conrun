<?php

namespace IMI\Contao\Command\Cache;

use IMI\Contao\Application;
use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class EnableCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        if ($application->getContaoMajorVersion() == Application::MAGENTO_MAJOR_VERSION_1) {
            $application->add(new EnableCommand());
            $command = $this->getApplication()->find('cache:enable');

            $commandTester = new CommandTester($command);
            $commandTester->execute(array('command' => $command->getName()));

            $this->assertRegExp('/Caches enabled/', $commandTester->getDisplay());
        }
    }

    public function testExecuteMultipleCaches()
    {
        $application = $this->getApplication();
        if ($application->getContaoMajorVersion() == Application::MAGENTO_MAJOR_VERSION_1) {
            $application->add(new DisableCommand());

            $command = $this->getApplication()->find('cache:enable');
            $commandTester = new CommandTester($command);
            $commandTester->execute(
                array(
                    'command' => $command->getName(),
                    'code'    => 'eav,config'
                )
            );

            $this->assertRegExp('/Cache config enabled/', $commandTester->getDisplay());
            $this->assertRegExp('/Cache eav enabled/', $commandTester->getDisplay());
        }
    }
}