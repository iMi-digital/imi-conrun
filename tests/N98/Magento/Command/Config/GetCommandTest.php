<?php

namespace IMI\Contao\Command\Config;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class GetCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new DumpCommand());
        $setCommand = $this->getApplication()->find('config:set');
        $getCommand = $this->getApplication()->find('config:get');

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

        $commandTester = new CommandTester($getCommand);
        $commandTester->execute(
            array(
                 'command' => $getCommand->getName(),
                 'path'    => 'imi_conrun/foo/bar',
            )
        );
        $this->assertContains('| imi_conrun/foo/bar | default | 0        | 1234  |', $commandTester->getDisplay());

        $commandTester->execute(
            array(
                 'command'         => $getCommand->getName(),
                 'path'            => 'imi_conrun/foo/bar',
                 '--update-script' => true
            )
        );
        $this->assertContains(
            "\$installer->setConfigData('imi_conrun/foo/bar', '1234');",
            $commandTester->getDisplay()
        );

        $commandTester->execute(
            array(
                 'command'          => $getCommand->getName(),
                 'path'             => 'imi_conrun/foo/bar',
                 '--conrun-script' => true
            )
        );
        $this->assertContains(
            "config:set imi_conrun/foo/bar --scope-id=0 --scope=default '1234'",
            $commandTester->getDisplay()
        );

        /**
         * Dump CSV
         */
        $commandTester->execute(
            array(
                'command'  => $getCommand->getName(),
                'path'     => 'imi_conrun/foo/bar',
                '--format' => 'csv',
            )
        );
        $this->assertContains('Path,Scope,Scope-ID,Value', $commandTester->getDisplay());
        $this->assertContains('imi_conrun/foo/bar,default,0,1234', $commandTester->getDisplay());

        /**
         * Dump XML
         */
        $commandTester->execute(
            array(
                'command'  => $getCommand->getName(),
                'path'     => 'imi_conrun/foo/bar',
                '--format' => 'xml',
            )
        );
        $this->assertContains('<table>', $commandTester->getDisplay());
        $this->assertContains('<Value>1234</Value>', $commandTester->getDisplay());

        /**
         * Dump XML
         */
        $commandTester->execute(
            array(
                'command'  => $getCommand->getName(),
                'path'     => 'imi_conrun/foo/bar',
                '--format' => 'json',
            )
        );
        $this->assertRegExp('/"Value":\s*"1234"/', $commandTester->getDisplay());
    }

}
