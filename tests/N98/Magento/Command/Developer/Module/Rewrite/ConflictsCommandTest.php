<?php

namespace IMI\Contao\Command\Developer\Module\Rewrite;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

/**
 * Class ConflictsCommandTest
 *
 * @TODO Check with simulated conflict
 * @package IMI\Contao\Command\Developer\Module\Rewrite
 */
class ConflictsCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new ConflictsCommand());
        $command = $this->getApplication()->find('dev:module:rewrite:conflicts');

        /**
         * Only stdout
         */
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
            )
        );
        $this->assertContains('No rewrite conflicts were found', $commandTester->getDisplay());


        /**
         * Junit Log without any output
         */
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'     => $command->getName(),
                '--log-junit' => '_output.xml',
            )
        );
        $this->assertEquals('', $commandTester->getDisplay());
        $this->assertFileExists('_output.xml');
        @unlink('_output.xml');
    }
}