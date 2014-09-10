<?php

namespace IMI\Contao\Command\Developer\Theme;

use IMI\Contao\Command\AbstractContaoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class InfoCommand
 * @codeCoverageIgnore Command is currently not implemented
 * @package IMI\Contao\Command\Developer\Theme
 */
class InfoCommand extends AbstractContaoCommand
{
    protected function configure()
    {
        $this
            ->setName('dev:theme:info')
            ->addArgument('theme', InputArgument::REQUIRED, 'Your theme')
            ->setDescription('Infos about a theme');
    }

    /**
     * @param \Symfony\Component\Console\Input\\Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\\Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output);
        if ($this->initContao()) {
        }
    }
}