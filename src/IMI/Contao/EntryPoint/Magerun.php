<?php

namespace IMI\Contao\EntryPoint;

/**
 * Class Contrun
 * This is required for Contao 2
 *
 * @codeCoverageIgnore
 * @package IMI\Contao\EntryPoint
 */
class Contrun extends \Mage_Core_Model_EntryPointAbstract
{
    /**
     * @param string $baseDir
     * @param array $params
     */
    public function __construct($baseDir, array $params = array())
    {
        $this->_params = $params;
        $config = new \Mage_Core_Model_Config_Primary($baseDir, $this->_params);
        parent::__construct($config);
    }

    public function processRequest()
    {
        // NOP
    }
}