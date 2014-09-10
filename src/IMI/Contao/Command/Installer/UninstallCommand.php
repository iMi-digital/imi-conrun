<?php

namespace IMI\Contao\Command\Installer;

use IMI\Contao\Command\AbstractContaoCommand;
use IMI\Util\Filesystem;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UninstallCommand
 *
 * @codeCoverageIgnore
 * @package IMI\Contao\Command\Installer
 */
class UninstallCommand extends AbstractContaoCommand
{
    protected function configure()
    {
        $this
            ->setName('uninstall')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force')
            ->addOption('installationFolder', null, InputOption::VALUE_OPTIONAL, 'Folder where Contao is currently installed')
            ->setDescription('Uninstall contao (drops database and empties current folder or folder set via installationFolder)')
        ;

        $help = <<<HELP
**Please be careful: This removes all data from your installation.**
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->chooseInstallationFolder($input, $output);
        $this->detectContao($output);
        $this->getApplication()->setAutoExit(false);
        $dialog = $this->getHelperSet()->get('dialog');
        /* @var $dialog \Symfony\Component\Console\Helper\DialogHelper */

        $shouldUninstall = $input->getOption('force');
        if (!$shouldUninstall) {
            $shouldUninstall = $dialog->askConfirmation($output, '<question>Really uninstall ?</question> <comment>[n]</comment>: ', false);
        }

        if ($shouldUninstall) {
            $input = new StringInput('db:drop --force');
            $this->getApplication()->run($input, $output);
            $fileSystem = new Filesystem();
            $output->writeln('<info>Remove directory </info><comment>' . $this->_contaoRootFolder . '</comment>');
            try {
                $fileSystem->recursiveRemoveDirectory($this->_contaoRootFolder);
            } catch (\Exception $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
            $output->writeln('<info>Done</info>');
        }
    }
}
