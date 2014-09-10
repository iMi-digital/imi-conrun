<?php

namespace IMI\Contao\Command\Developer\Log;

use IMI\Contao\Command\AbstractContaoConfigCommand;

class LogCommand extends AbstractContaoConfigCommand
{
    /**
     * @var string
     */
    protected $commandName = 'dev:log';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggle development log (system.log, exception.log)';

    /**
     * @var string
     */
    protected $toggleComment = 'Development Log';

    /**
     * @var string
     */
    protected $configPath = 'dev/log/active';

    /**
     * @var string
     */
    protected $scope = self::SCOPE_STORE_VIEW_GLOBAL;
}