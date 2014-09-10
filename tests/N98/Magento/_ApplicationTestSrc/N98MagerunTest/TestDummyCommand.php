<?php

namespace IMIContrunTest;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestDummyCommand extends \IMI\Contao\Command\AbstractContaoCommand
{
    protected function configure()
    {
        $this
            ->setName('imiconruntest:test:dummy')
            ->setDescription('Dummy command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output);
        if ($this->initContao()) {
            $output->writeln('dummy');
        }
    }
}