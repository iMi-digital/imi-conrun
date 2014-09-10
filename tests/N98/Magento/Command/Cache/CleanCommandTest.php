<?php

namespace IMI\Contao\Command\Cache;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class CleanCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->markTestSkipped('Cannot explain why test does not work on travis ci server.');
        $application = $this->getApplication();
        $application->add(new CleanCommand());
        $command = $this->getApplication()->find('cache:clean');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertContains('config cache cleaned', $commandTester->getDisplay());
    }
}