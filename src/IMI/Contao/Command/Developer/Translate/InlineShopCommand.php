<?php

namespace IMI\Contao\Command\Developer\Translate;

use IMI\Contao\Command\AbstractContaoConfigCommand;

class InlineShopCommand extends AbstractContaoConfigCommand
{
    /**
     * @var string
     */
    protected $configPath = 'dev/translate_inline/active';

    /**
     * @var string
     */
    protected $toggleComment = 'Inline Translation';

    /**
     * @var string
     */
    protected $commandName = 'dev:translate:shop';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggle inline translation tool for shop';
}