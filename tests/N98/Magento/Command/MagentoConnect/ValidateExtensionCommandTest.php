<?php

namespace IMI\Contao\Command\ContaoConnect;
use IMI\Contao\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ValidateExtensionCommandTest extends TestCase
{
    public function testSetup()
    {
        $this->getApplication()->initContao();
        if (version_compare(\Mage::getVersion(), '1.4.2.0', '<=')) {
            $this->markTestSkipped('Skip Test - mage cli script does not exist.');
        }

        $application = $this->getApplication();
        $commandMock = $this->getMockBuilder('IMI\Contao\Command\ContaoConnect\ValidateExtensionCommand')
            ->setMockClassName('ValidateExtensionCommandMock')
            ->enableOriginalClone()
            ->setMethods(array('_getDownloaderConfigPath'))
            ->getMock();
        $application->add($commandMock);

        $commandMock
            ->expects($this->any())
            ->method('_getDownloaderConfigPath')
            ->will($this->returnValue(__DIR__ . '/_files/cache.cfg'));

        $commandTester = new CommandTester($commandMock);
        $commandTester->execute(
            array(
                'command'           => $commandMock->getName(),
                'package'           => 'Mage_All_Latest',
                '--include-default' => true
            )
        );
        
        $output = $commandTester->getDisplay();
        $this->assertContains('Mage_All_Latest', $output);
    }
}
