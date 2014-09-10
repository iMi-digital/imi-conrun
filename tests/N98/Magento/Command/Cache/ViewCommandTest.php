<?php

namespace IMI\Contao\Command\Cache;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class ViewCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new ListCommand());
        $command = $this->getApplication()->find('cache:view');

        \Mage::app()->getCache()->save('TEST imi-conrun', 'imi-conrun-unittest');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'id'      => 'imi-conrun-unittest'
            )
        );

        $this->assertRegExp('/TEST imi-conrun/', $commandTester->getDisplay());
    }

    public function testExecuteUnserialize()
    {
        $application = $this->getApplication();
        $application->add(new ListCommand());
        $command = $this->getApplication()->find('cache:view');

        $cacheData = array(
            1,
            2,
            3,
            'foo' => array('bar')
        );
        \Mage::app()->getCache()->save(serialize($cacheData), 'imi-conrun-unittest');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'       => $command->getName(),
                'id'            => 'imi-conrun-unittest',
                '--unserialize' => true,
            )
        );

        $this->assertEquals(print_r($cacheData, true) . "\n", $commandTester->getDisplay());
    }
}