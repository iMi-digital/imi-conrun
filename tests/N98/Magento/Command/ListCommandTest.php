<?php

namespace IMI\Contao\Command;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $command = $this->getApplication()->find('list');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => 'list'
            )
        );
    
        $this->assertContains(
            sprintf('imi-conrun version %s by netz98 new media GmbH', $this->getApplication()->getVersion()),
            $commandTester->getDisplay()
        );
    }
}