<?php

namespace IMI\Contao\Command;

use IMI\Contao\Command\AbstractContaoCommand;
use Symfony\Component\Console\Shell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShellCommand extends AbstractContaoCommand
{
    protected function configure()
    {
        $this
            ->setName('shell')
            ->setDescription('Runs imi-conrun as shell')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shell = new Shell($this->getApplication());
        $shell->run();
    }
}
