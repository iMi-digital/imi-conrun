<?php

namespace IMI\Contao\Command\Developer\Translate;

use IMI\Contao\Command\AbstractContaoConfigCommand;

class InlineAdminCommand extends AbstractContaoConfigCommand
{
    /**
     * @var string
     */
    protected $configPath = 'dev/translate_inline/active_admin';

    /**
     * @var string
     */
    protected $toggleComment = 'Inline Translation (Admin)';

    /**
     * @var string
     */
    protected $commandName = 'dev:translate:admin';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggle inline translation tool for admin';

    /**
     * @var string
     */
    protected $scope = self::SCOPE_GLOBAL;
}