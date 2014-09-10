<?php

namespace IMI\Contao\Command\Indexer;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class ReindexCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new ReindexCommand());
        $command = $this->getApplication()->find('index:reindex');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'    => $command->getName(),
                'index_code' => 'tag_summary,tag_summary', // run index twice
            )
        );
    
        $this->assertContains('Successfully reindexed tag_summary', $commandTester->getDisplay());
    }
}