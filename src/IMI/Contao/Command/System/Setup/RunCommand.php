<?php

namespace IMI\Contao\Command\System\Setup;

use IMI\Contao\Command\AbstractContaoCommand;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class RunCommand extends AbstractContaoCommand
{
    protected function configure()
    {
        $this
            ->setName('sys:setup:run')
            ->setDescription('Runs all new database update scripts.')
            ->addOption('with-drops', null, InputOption::VALUE_NONE, 'Also execute DROP statements')
            ->addOption('skip-runonce', null, InputOption::VALUE_NONE, 'Do not execute runonce.php files');
        $help = <<<HELP
Run SQL updates and runonce.php scripts, can optionally include DROP statements.
This command is useful if you update your system with enabled maintenance mode.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param InputInterface   $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->setAutoExit(false);
        $this->detectContao($output);
        if (!$this->initContao()) {
            return false;
        }

        $this->detectContao($output);
        if (!$this->initContao()) {
            return;
        }

        $withDrops = $input->getOption('with-drops');

        $installer = new \IMI\Contao\System\Installer();
        $installer->runUpdates($withDrops);

        $withoutRunonce = $input->getOption('skip-runonce');

        if (!$withoutRunonce) {
            $backend = new \IMI\Contao\System\Backend();
            $backend->runRunOnce();
        }

        $output->writeln('<info>done</info>');

        return 0;
    }

}
