<?php

namespace IMI\Contao\Command\System\Website;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new ListCommand());
        $command = $this->getApplication()->find('sys:website:list');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('/Contao Websites/', $commandTester->getDisplay());
        $this->assertRegExp('/id/', $commandTester->getDisplay());
        $this->assertRegExp('/code/', $commandTester->getDisplay());
    }
}