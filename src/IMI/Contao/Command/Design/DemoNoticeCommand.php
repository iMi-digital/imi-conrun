<?php

namespace IMI\Contao\Command\Design;

use IMI\Contao\Command\AbstractContaoConfigCommand;

class DemoNoticeCommand extends AbstractContaoConfigCommand
{
    /**
     * @var string
     */
    protected $configPath = 'design/head/demonotice';

    /**
     * @var string
     */
    protected $toggleComment = 'Demo Notice';

    /**
     * @var string
     */
    protected $commandName = 'design:demo-notice';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggles demo store notice for a store view';

    protected $scope = self::SCOPE_STORE_VIEW_GLOBAL;
}