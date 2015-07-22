<?php

namespace IMI\Contao\Command\System\Setup;

use IMI\Contao\Command\AbstractContaoCommand;
use IMI\JUnitXml\Document as JUnitXmlDocument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use IMI\Util\Console\Helper\Table\Renderer\RendererFactory;

class PreviewCommand extends AbstractContaoCommand
{
    protected function configure()
    {
        $this
            ->setName('sys:setup:preview')
            ->setDescription('Compare module version with core_resource table.')
            ->addOption('skip-drops', null, InputOption::VALUE_NONE, 'Do not list DROP statements')
            ->addOption('skip-runonce', null, InputOption::VALUE_NONE, 'Do not list runonce.php files');

        $help = <<<HELP
Preview SQL and runonce.php updates.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output);
        if (!$this->initContao()) {
            return;
        }

        $installer = new \IMI\Contao\System\Installer();

        $commands = $installer->getSql(!$input->getOption('skip-drops'));

        $backend = new \IMI\Contao\System\Backend();

        if (count($commands) > 0) {
            $this->writeSection($output, 'Planned SQL Updates');

            foreach ($commands as $key=>$section) {
                foreach($section as $command) {
                    if ($key == 'ALTER_DROP' ||
                        $key == 'DROP') {
                        $output->writeln('<fg=red>' . $command . '</fg=red>');
                    } else {
                        $output->writeln($command);
                    }
                }
            }
        } else {
            $this->writeSection($output, 'No SQL Updates Found', 'info');
        }

        if (!$input->getOption('skip-runonce')) {
            $runOnceList = $backend->listRunOnce();

            if (count($runOnceList) > 0) {
                $this->writeSection($output, 'Planned RunOnce Updates');

                foreach($runOnceList as $runOnceFile) {
                    $output->writeln($runOnceFile);
                }
            } else {
                $this->writeSection($output, 'No RunOnce Updates Found', 'info');
            }
        }

        return;
    }

}
