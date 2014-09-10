<?php

namespace TestModule;

use IMI\Contao\Command\AbstractContaoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FooCommand extends AbstractContaoCommand
{
    protected function configure()
    {
      $this
          ->setName('testmodule:foo')
          ->setDescription('Test command registered in a module')
      ;
    }

   /**
    * @param \Symfony\Component\Console\Input\InputInterface $input
    * @param \Symfony\Component\Console\Output\OutputInterface $output
    * @return int|void
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output);
        if ($this->initContao()) {
            
        }
    }
}
