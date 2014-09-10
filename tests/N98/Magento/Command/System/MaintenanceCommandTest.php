<?php

namespace IMI\Contao\Command\System;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class MaintenanceCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new MaintenanceCommand());
        $command = $this->getApplication()->find('sys:maintenance');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--on'    => '',
            )
        );
        $this->assertRegExp('/Maintenance mode on/', $commandTester->getDisplay());
        $this->assertFileExists($this->getApplication()->getContaoRootFolder() . '/maintenance.flag');

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--off'   => '',
            )
        );
        $this->assertRegExp('/Maintenance mode off/', $commandTester->getDisplay());
        $this->assertFileNotExists($this->getApplication()->getContaoRootFolder() . '/maintenance.flag');
    }
}