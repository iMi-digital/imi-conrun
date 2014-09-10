<?php

namespace Acme;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use IMI\Contao\Command\AbstractContaoCommand;

class FooCommand extends AbstractContaoCommand
{
    protected function configure()
    {
        $this->setName('acme:foo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}