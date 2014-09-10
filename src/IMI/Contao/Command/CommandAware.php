<?php

namespace IMI\Contao\Command;

use Symfony\Component\Console\Command\Command;

interface CommandAware
{
    /**
     * @param Command $command
     */
    public function setCommand(Command $command);
}