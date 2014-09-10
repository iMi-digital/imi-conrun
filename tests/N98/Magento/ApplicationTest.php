<?php

namespace IMI\Contao;

use IMI\Util\ArrayFunctions;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;
use Symfony\Component\Yaml\Yaml;
use org\bovigo\vfs\vfsStream;

class ApplicationTest extends TestCase
{
    public function testExecute()
    {
        /**
         * Check autoloading
         */
        $application = require __DIR__ . '/../../../src/bootstrap.php';
        $application->setContaoRootFolder(getenv('IMI_MAGERUN_TEST_MAGENTO_ROOT'));

        /* @var $application Application */
        $this->assertInstanceOf('\IMI\Contao\Application', $application);
        $loader = $application->getAutoloader();
        $this->assertInstanceOf('\Composer\Autoload\ClassLoader', $loader);

        /**
         * Check version
         */
        $this->assertEquals(\IMI\Contao\Application::APP_VERSION, trim(file_get_contents(__DIR__ . '/../../../version.txt')));

        /* @var $loader \Composer\Autoload\ClassLoader */
        $prefixes = $loader->getPrefixes();
        $this->assertArrayHasKey('IMI', $prefixes);

        $distConfigArray = Yaml::parse(file_get_contents(__DIR__ . '/../../../config.yaml'));

        $configArray = array(
            'autoloaders' => array(
                'IMIContrunTest' => __DIR__ . '/_ApplicationTestSrc',
            ),
            'commands' => array(
                'customCommands' => array(
                    0 => 'IMIContrunTest\TestDummyCommand'
                ),
                'aliases' => array(
                    array(
                        'cl' => 'cache:list'
                    )
                ),
            ),
            'init' => array(
                'options' => array(
                    'config_model' => 'IMIContrunTest\AlternativeConfigModel',
                )
            )
        );

        $application->setAutoExit(false);
        $application->init(ArrayFunctions::mergeArrays($distConfigArray, $configArray));
        $application->run(new StringInput('list'), new NullOutput());

        // Check if autoloaders, commands and aliases are registered
        $prefixes = $loader->getPrefixes();
        $this->assertArrayHasKey('IMIContrunTest', $prefixes);

        $testDummyCommand = $application->find('imiconruntest:test:dummy');
        $this->assertInstanceOf('\IMIContrunTest\TestDummyCommand', $testDummyCommand);

        $commandTester = new CommandTester($testDummyCommand);
        $commandTester->execute(
            array(
                'command'    => $testDummyCommand->getName(),
            )
        );
        $this->assertContains('dummy', $commandTester->getDisplay());
        $this->assertTrue($application->getDefinition()->hasOption('root-dir'));

        // Test alternative config model
        $application->initContao();
        if (version_compare(\Mage::getVersion(), '1.7.0.2', '>=')) {
            // config_model option is only available in Contao CE >1.6
            $this->assertInstanceOf('\IMIContrunTest\AlternativeConfigModel', \Mage::getConfig());
        }


        // check alias
        $this->assertInstanceOf('\IMI\Contao\Command\Cache\ListCommand', $application->find('cl'));
    }

    public function testPlugins()
    {
        /**
         * Check autoloading
         */
        $application = require __DIR__ . '/../../../src/bootstrap.php';
        $application->setContaoRootFolder(getenv('IMI_MAGERUN_TEST_MAGENTO_ROOT'));

        // Load plugin config
        $injectConfig = array(
            'plugin' => array(
                'folders' => array(
                    __DIR__ . '/_ApplicationTestModules'
                )
            )
        );
        $application->init($injectConfig);

        // Check for module command
        $this->assertInstanceOf('TestModule\FooCommand', $application->find('testmodule:foo'));
    }

    public function testComposer()
    {
        vfsStream::setup('root');
        vfsStream::create(
            array(
                'htdocs' => array(
                    'app' => array(
                        'Mage.php' => ''
                    )
                ),
                'vendor' => array(
                    'acme' => array(
                        'conrun-test-module' => array(
                            'imi-conrun.yaml' => file_get_contents(__DIR__ . '/_ApplicationTestComposer/imi-conrun.yaml'),
                            'src' => array(
                                'Acme' => array(
                                    'FooCommand.php' => file_get_contents(__DIR__ . '/_ApplicationTestComposer/FooCommand.php'),
                                )
                            )
                        )
                    ),
                    'imi' => array(
                        'conrun' => array(
                            'src' => array(
                                'IMI' => array(
                                    'Contao' => array(
                                        'Command' => array(
                                            'ConfigurationLoader.php' => '',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            )
        );

        $configurationLoader = $this->getMock(
            '\IMI\Contao\Command\ConfigurationLoader',
            array('getConfigurationLoaderDir'),
            array(array(), false, new NullOutput())
        );
        $configurationLoader
            ->expects($this->any())
            ->method('getConfigurationLoaderDir')
            ->will($this->returnValue(vfsStream::url('root/vendor/imi/conrun/src/IMI/Contao/Command')));

        $application = require __DIR__ . '/../../../src/bootstrap.php';
        /* @var $application Application */
        $application->setContaoRootFolder(vfsStream::url('root/htdocs'));
        $application->setConfigurationLoader($configurationLoader);
        $application->init();

        // Check for module command
        $this->assertInstanceOf('Acme\FooCommand', $application->find('acme:foo'));
    }
}