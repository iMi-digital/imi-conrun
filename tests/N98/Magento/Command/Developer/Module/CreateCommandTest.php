<?php

namespace IMI\Contao\Command\Developer\Module;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;
use IMI\Util\Filesystem;

class CreateCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new ListCommand());
        $command = $this->getApplication()->find('dev:module:create');

        $root = getcwd();

        // delete old module
        if (is_dir($root . '/IMIContrun_UnitTest')) {
            $filesystem = new Filesystem();
            $filesystem->recursiveRemoveDirectory($root . '/IMIContrun_UnitTest');
            clearstatcache();
        }

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'   => $command->getName(),
                '--add-all'       => true,
                '--add-setup'     => true,
                '--add-readme'    => true,
                '--add-composer'  => true,
                '--modman'        => true,
                '--description'   => 'Unit Test Description',
                '--author-name'   => 'Unit Test',
                '--author-email'  => 'imi-conrun@example.com',
                'vendorNamespace' => 'IMIContrun',
                'moduleName'      => 'UnitTest'
            )
        );

        $this->assertFileExists($root . '/IMIContrun_UnitTest/composer.json');
        $this->assertFileExists($root . '/IMIContrun_UnitTest/readme.md');
        $moduleBaseFolder = $root . '/IMIContrun_UnitTest/src/app/code/local/IMIContrun/UnitTest/';
        $this->assertFileExists($moduleBaseFolder . 'etc/config.xml');
        $this->assertFileExists($moduleBaseFolder . 'Block');
        $this->assertFileExists($moduleBaseFolder . 'Model');
        $this->assertFileExists($moduleBaseFolder . 'Helper');
        $this->assertFileExists($moduleBaseFolder . 'data/imiconrun_unittest_setup');
        $this->assertFileExists($moduleBaseFolder . 'sql/imiconrun_unittest_setup');

        // delete old module
        if (is_dir($root . '/IMIContrun_UnitTest')) {
            $filesystem = new Filesystem();
            $filesystem->recursiveRemoveDirectory($root . '/IMIContrun_UnitTest');
            clearstatcache();
        }
    }
}