<?php

namespace IMI\Contao\Command\Developer;

use IMI\Contao\Command\AbstractContaoConfigCommand;

class TemplateHintsCommand extends AbstractContaoConfigCommand
{
    /**
     * @var string
     */
    protected $commandName = 'dev:template-hints';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggles template hints';

    /**
     * @var string
     */
    protected $toggleComment = 'Template Hints';

    /**
     * @var string
     */
    protected $configPath = 'dev/debug/template_hints';

    /**
     * @var string
     */
    protected $scope = self::SCOPE_STORE_VIEW;
}