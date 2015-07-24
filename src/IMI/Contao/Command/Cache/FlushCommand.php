<?php

namespace IMI\Contao\Command\Cache;

use IMI\Contao\Application;
use IMI\Contao\System\PurgeData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FlushCommand extends AbstractCacheCommand
{
    protected function configure()
    {
        $this
            ->setName('cache:flush')
            ->setDescription('Clean all cache folders')
        ;
    }

    public function isEnabled()
    {
        return $this->getApplication()->getContaoMajorVersion() == Application::MAGENTO_MAJOR_VERSION_1;
    }

    /**
     * Check which folder caches are not clean
     */
    protected function getDirty()
    {
        $purgeData = new PurgeData();
        $jobs = $purgeData->getJobs();

        $dirty = array();
        foreach($jobs as $key=>$job) {
            if ($job['group'] == 'folders' && $job['count'] > 0) {
                $dirty[] = $key;
            }

        }

        return $dirty;
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
            foreach($GLOBALS['TL_PURGE']['folders'] as $job=>$data) {
                $callback = $data['callback'];
                $class = new $callback[0];
                $class->$callback[1]();
            };

            $dirty = $this->getDirty();

            if (count($dirty) == 0) {
                $output->writeln('<info>Caches folders cleared</info>');
            } else {
                $output->writeln('<error>Some cache folders could not be cleared. Permission problems? You might be able to fix this by setting an umask or using another user.</error>');
                $output->writeln('<info>Dirty caches:</info> ' . implode(', ', $dirty));
            }
        }
    }
}