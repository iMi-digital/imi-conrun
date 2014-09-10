<?php

namespace IMI\Contao\Command\Cache;

use IMI\Contao\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FlushCommand extends AbstractCacheCommand
{
    protected function configure()
    {
        $this
            ->setName('cache:flush')
            ->setDescription('Flush contao cache storage')
        ;
    }

    public function isEnabled()
    {
        return $this->getApplication()->getContaoMajorVersion() == Application::MAGENTO_MAJOR_VERSION_1;
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

            \Mage::app()->loadAreaPart('adminhtml', 'events');
            \Mage::dispatchEvent('adminhtml_cache_flush_all', array('output' => $output));
            \Mage::app()->getCacheInstance()->flush();
            $output->writeln('<info>Cache cleared</info>');

            /* Since Contao 1.10 we have an own cache handler for FPC */
            if ($this->_contaoEnterprise && version_compare(\Mage::getVersion(), '1.11.0.0', '>=')) {
                \Enterprise_PageCache_Model_Cache::getCacheInstance()->flush();
                $output->writeln('<info>FPC cleared</info>');
            }

        }
    }
}