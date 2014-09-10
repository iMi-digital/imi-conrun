<?php

namespace IMI\Contao\Command\Developer;

use IMI\Contao\Command\AbstractContaoConfigCommand;

class ProfilerCommand extends AbstractContaoConfigCommand
{
    /**
     * @var string
     */
    protected $commandName = 'dev:profiler';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggles profiler for debugging';

    /**
     * @var string
     */
    protected $configPath = 'dev/debug/profiler';

    /**
     * @var string
     */
    protected $toggleComment = 'Profiler';

    /**
     * @var string
     */
    protected $scope = self::SCOPE_STORE_VIEW_GLOBAL;
}