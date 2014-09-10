<?php

namespace IMI\Contao\Command\PHPUnit;

use IMI\Contao\Application;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class TestCase
 *
 * @codeCoverageIgnore
 * @package IMI\Contao\Command\PHPUnit
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \IMI\Contao\Application
     */
    private $application = null;

    /**
     * @throws \RuntimeException
     * @return PHPUnit_Framework_MockObject_MockObject|\IMI\Contao\Application
     */
    public function getApplication()
    {
        if ($this->application === null) {
            $root = getenv('IMI_MAGERUN_TEST_MAGENTO_ROOT');
            if (empty($root)) {
                throw new \RuntimeException(
                    'Please specify environment variable IMI_MAGERUN_TEST_MAGENTO_ROOT with path to your test
                    contao installation!'
                );
            }

            $this->application = $this->getMock(
                'IMI\Contao\Application',
                array('getContaoRootFolder')
            );
            $loader = require __DIR__ . '/../../../../../vendor/autoload.php';
            $this->application->setAutoloader($loader);
            $this->application->expects($this->any())->method('getContaoRootFolder')->will($this->returnValue($root));
            $this->application->init();
            $this->application->initContao();
            if ($this->application->getContaoMajorVersion() == Application::MAGENTO_MAJOR_VERSION_1) {
                spl_autoload_unregister(array(\Varien_Autoload::instance(), 'autoload'));
            }
        }

        return $this->application;
    }

    /**
     * @return \Varien_Db_Adapter_Pdo_Mysql
     */
    public function getDatabaseConnection()
    {
        return \Mage::getSingleton('core/resource')->getConnection('write');
    }
}
