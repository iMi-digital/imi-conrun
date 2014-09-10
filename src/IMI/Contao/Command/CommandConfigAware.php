<?php

namespace IMI\Contao\Command;

interface CommandConfigAware
{
    /**
     * @param array $commandConfig
     */
    public function setCommandConfig(array $commandConfig);
}