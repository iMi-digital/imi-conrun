<?php

namespace IMI\Contao\Command\Cache;

use IMI\Contao\Command\AbstractContaoCommand;

class AbstractCacheCommand extends AbstractContaoCommand
{
    /**
     * @return Mage_Core_Model_Cache
     */
    protected function _getCacheModel()
    {
        return $this->_getModel('core/cache', 'Mage_Core_Model_Cache');
    }
}
