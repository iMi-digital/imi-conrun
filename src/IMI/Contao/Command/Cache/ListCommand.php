<?php

namespace IMI\Contao\Command\Cache;

use IMI\Contao\System\PurgeData;
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
            ->setDescription('Lists all Contao caches')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output, true);

        if (!$this->initContao()) {
            return;
        }

        $purgeData = new PurgeData();
        $jobs = $purgeData->getJobs();
        $grouped = array(
            'tables' => array(), 'files' => array(), 'custom' => array()
        );

        foreach ($jobs as $key => $job) {
            $grouped[$job['group']][$key] = $job;
        }

        foreach ($grouped as $groupKey => $jobs) {
            $table = array();
            foreach ($jobs as $key=>$job) {
                $table[] = array(
                    $key,
                    ucwords(str_replace('Purge the ', '', $job['title'])),
                    $job['count'],
                    $job['size'],
                );
            }

            $this->writeSection($output, ucfirst($groupKey));

            $this->getHelper('table')
                ->setHeaders(array('code', 'title', 'count', 'size'))
                ->renderByFormat($output, $table, $input->getOption('format'));
        }


    }
}