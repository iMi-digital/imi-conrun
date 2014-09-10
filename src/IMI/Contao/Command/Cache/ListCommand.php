<?php

namespace IMI\Contao\Command\Cache;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use IMI\Util\Console\Helper\Table\Renderer\RendererFactory;

class ListCommand extends AbstractCacheCommand
{
    protected function configure()
    {
        $this
            ->setName('cache:list')
            ->setDescription('Lists all contao caches')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output, true);

        if ($this->initContao()) {
            $cacheTypes = $this->_getCacheModel()->getTypes();
            $table = array();
            foreach ($cacheTypes as $cacheCode => $cacheInfo) {
                $table[] = array(
                    $cacheCode,
                    $cacheInfo['status'] ? 'enabled' : 'disabled'
                );
            }

            $this->getHelper('table')
                ->setHeaders(array('code', 'status'))
                ->renderByFormat($output, $table, $input->getOption('format'));
        }
    }
}