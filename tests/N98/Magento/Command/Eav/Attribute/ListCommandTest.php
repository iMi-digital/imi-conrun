<?php

namespace IMI\Contao\Command\Eav\Attribute;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new ListCommand());
        $command = $this->getApplication()->find('eav:attribute:list');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'       => $command->getName(),
                '--filter-type' => 'catalog_product',
                '--add-source'  => true,
            )
        );

        $this->assertContains('eav/entity_attribute_source_boolean', $commandTester->getDisplay());
        $this->assertContains('sku', $commandTester->getDisplay());
        $this->assertContains('catalog_product', $commandTester->getDisplay());
    }
}
