<?php

namespace IMI\Contao\Command\Eav\Attribute;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;

class ViewCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new ListCommand());
        $command = $this->getApplication()->find('eav:attribute:view');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'       => $command->getName(),
                'entityType'    => 'catalog_product',
                'attributeCode' => 'sku',
            )
        );

        $this->assertContains('sku', $commandTester->getDisplay());
        $this->assertContains('catalog_product_entity', $commandTester->getDisplay());
        $this->assertContains('Backend-Type', $commandTester->getDisplay());
        $this->assertContains('static', $commandTester->getDisplay());
    }
}