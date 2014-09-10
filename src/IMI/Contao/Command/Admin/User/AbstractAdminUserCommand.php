<?php

namespace IMI\Contao\Command\Admin\User;

use IMI\Contao\Command\AbstractContaoCommand;

abstract class AbstractAdminUserCommand extends AbstractContaoCommand
{
    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function getUserModel()
    {
        return $this->_getModel('admin/user', 'Mage_User_Model_User');
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function getRoleModel()
    {
        return $this->_getModel('admin/roles', 'Mage_User_Model_Role');
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function getRulesModel()
    {
        return $this->_getModel('admin/rules', 'Mage_User_Model_Rules');
    }
}
