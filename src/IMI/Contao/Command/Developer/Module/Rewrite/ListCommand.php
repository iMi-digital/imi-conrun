<?php

namespace IMI\Contao\Command\Developer\Module\Rewrite;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use IMI\Util\Console\Helper\Table\Renderer\RendererFactory;

class ListCommand extends AbstractRewriteCommand
{
    protected function configure()
    {
        $this
            ->setName('dev:module:rewrite:list')
            ->setDescription('Lists all contao rewrites')
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
        $this->detectContao($output, true);
        if ($this->initContao()) {

            $rewrites = array_merge($this->loadRewrites(), $this->loadAutoloaderRewrites());

            $table = array();
            foreach ($rewrites as $type => $data) {
                if (count($data) > 0) {
                    foreach ($data as $class => $rewriteClass) {
                        $table[] = array(
                            $type,
                            $class,
                            implode(', ', $rewriteClass)
                        );
                    }
                }
            }

            if (count($table) === 0 && $input->getOption('format') === null) {
                $output->writeln('<info>No rewrites were found.</info>');
            } else {
                if (count($table) == 0) {
                    $table = array();
                }
                $this->getHelper('table')
                    ->setHeaders(array('Type', 'Class', 'Rewrite'))
                    ->setRows($table)
                    ->renderByFormat($output, $table, $input->getOption('format'));
            }
        }
    }
}
