<?php

namespace IMI\Contao\Command\Cache;

use IMI\Contao\Application;
use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class FlushCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        if ($application->getContaoMajorVersion() == Application::MAGENTO_MAJOR_VERSION_1) {
            $application = $this->getApplication();
            $application->add(new FlushCommand());
            $command = $this->getApplication()->find('cache:flush');

            $commandTester = new CommandTester($command);
            $commandTester->execute(array('command' => $command->getName()));

            $this->assertRegExp('/Cache cleared/', $commandTester->getDisplay());
        }
    }
}