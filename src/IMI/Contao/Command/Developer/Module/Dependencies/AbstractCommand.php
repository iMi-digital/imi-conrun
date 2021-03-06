<?php

namespace IMI\Contao\Command\Developer\Module\Dependencies;

use Installer\Exception;
use IMI\Contao\Command\AbstractContaoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use IMI\Util\Console\Helper\Table\Renderer\RendererFactory;

abstract class AbstractCommand extends AbstractContaoCommand
{
    /**#@+
     * Command texts to output
     *
     * @var string
     */
    const COMMAND_NAME               = '';
    const COMMAND_DESCRIPTION        = '';
    const COMMAND_SECTION_TITLE_TEXT = '';
    const COMMAND_NO_RESULTS_TEXT    = '';
    /**#@-*/

    /**
     * Array of contao modules found in config
     *
     * @var array
     */
    protected $modules;

        /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName(static::COMMAND_NAME)
            ->addArgument('moduleName', InputArgument::REQUIRED, 'Module to show dependencies')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Show all dependencies (dependencies of dependencies)')
            ->setDescription(static::COMMAND_DESCRIPTION)
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $moduleName = $input->getArgument('moduleName');
        $recursive  = $input->getOption('all');
        if ($input->getOption('format') === null) {
            $this->writeSection($output, sprintf(static::COMMAND_SECTION_TITLE_TEXT, $moduleName));
        }
        $this->detectContao($output, true);
        $this->initContao();

        try {
            $dependencies = $this->findModuleDependencies($moduleName, $recursive);
            if (!empty($dependencies)) {
                usort($dependencies, array($this, 'sortDependencies'));
                $this->getHelper('table')
                    ->setHeaders(array('Name', 'Status', 'Current installed version', 'Code pool'))
                    ->setPadType(STR_PAD_LEFT)
                    ->renderByFormat($output, $dependencies, $input->getOption('format'));
            } else {
                $output->writeln(sprintf(static::COMMAND_NO_RESULTS_TEXT, $moduleName));
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }

    /**
     * Find dependencies of given module $moduleName.
     * If $recursive = true, dependencies will be collected recursively for all module dependencies
     *
     * @param string $moduleName
     * @param bool $recursive
     * @return array
     * @throws \InvalidArgumentException
     */
    abstract protected function findModuleDependencies($moduleName, $recursive = false);

    /**
     * Sort dependencies list by module name ascending
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    private function sortDependencies(array $a, array $b)
    {
        return strcmp($a[0], $b[0]);
    }
}
