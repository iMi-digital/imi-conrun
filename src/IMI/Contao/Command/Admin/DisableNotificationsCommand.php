<?php

namespace IMI\Contao\Command\Admin;

use IMI\Contao\Command\AbstractContaoConfigCommand;

class DisableNotificationsCommand extends AbstractContaoConfigCommand
{
    /**
     * @var string
     */
    protected $commandName = 'admin:notifications';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggles admin notifications';

    /**
     * @var string
     */
    protected $configPath = 'advanced/modules_disable_output/Mage_AdminNotification';

    /**
     * @var string
     */
    protected $toggleComment = 'Admin Notifications';

    /**
     * @var string
     */
    protected $trueName = 'hidden';

    /**
     * @var string
     */
    protected $falseName = 'visible';

    /**
     * @var string
     */
    protected $scope = self::SCOPE_GLOBAL;
}