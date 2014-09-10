<?php

namespace IMI\Contao\Command\Config;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class DeleteCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new DumpCommand());
        $setCommand = $this->getApplication()->find('config:set');
        $deleteCommand = $this->getApplication()->find('config:delete');

        /**
         * Add a new entry
         */
        $commandTester = new CommandTester($setCommand);
        $commandTester->execute(
            array(
                'command' => $setCommand->getName(),
                'path'    => 'imi_conrun/foo/bar',
                'value'   => '1234',
            )
        );
        $this->assertContains('imi_conrun/foo/bar => 1234', $commandTester->getDisplay());

        $commandTester = new CommandTester($deleteCommand);
        $commandTester->execute(
            array(
                'command' => $deleteCommand->getName(),
                'path'    => 'imi_conrun/foo/bar',
            )
        );
        $this->assertContains('| imi_conrun/foo/bar | default | 0  |', $commandTester->getDisplay());


        /**
         * Delete all
         */

        foreach (\Mage::app()->getStores() as $store) {
            // add multiple entries
            $commandTester = new CommandTester($setCommand);
            $commandTester->execute(
                array(
                     'command'    => $setCommand->getName(),
                     'path'       => 'imi_conrun/foo/bar',
                     '--scope'    => 'stores',
                     '--scope-id'  => $store->getId(),
                     'value'      => 'store-' . $store->getId(),
                )
            );
        }

        $commandTester = new CommandTester($deleteCommand);
        $commandTester->execute(
            array(
                 'command' => $deleteCommand->getName(),
                 'path'    => 'imi_conrun/foo/bar',
                 '--all'   => true,
            )
        );

        foreach (\Mage::app()->getStores() as $store) {
            $this->assertContains('| imi_conrun/foo/bar | stores   | ' . $store->getId() . '  |', $commandTester->getDisplay());
        }

    }
}
