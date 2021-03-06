<?php

namespace IMI\Contao\Command\System;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class InfoCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new InfoCommand());
        $command = $this->getApplication()->find('sys:info');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('/Contao System Information/', $commandTester->getDisplay());
        $this->assertRegExp('/Install Date/', $commandTester->getDisplay());
        $this->assertRegExp('/Crypt Key/', $commandTester->getDisplay());
    }
}