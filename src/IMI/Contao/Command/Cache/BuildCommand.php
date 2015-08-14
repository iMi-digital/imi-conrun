<?php

namespace IMI\Contao\Command\Cache;

use IMI\Contao\Application;
use IMI\Contao\System\PurgeData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends AbstractCacheCommand
{
    protected function configure()
    {
        $this
            ->setName('cache:build')
            ->setDescription('Build internal cache')
        ;
    }

    /*
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output, true);
        if ($this->initContao()) {
            {
                $automator = new \Automator();;
                $automator->generateInternalCache();
                $output->writeln('<info>Internal cache generated</info>');
            }
        }
    }
}