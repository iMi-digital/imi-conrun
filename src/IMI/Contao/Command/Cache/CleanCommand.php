<?php

namespace IMI\Contao\Command\Cache;

use IMI\Contao\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanCommand extends AbstractCacheCommand
{
    protected function configure()
    {
        $this
            ->setName('cache:clean')
            ->addArgument('type', InputArgument::OPTIONAL, 'Cache type code like "config"')
            ->setDescription('Clean contao cache')
        ;

        $help = <<<HELP
Cleans expired cache entries.
If you like to remove all entries use `cache:flush`
Or only one cache type like i.e. full_page cache:

   $ imi-conrun.phar cache:clean full_page

HELP;
        $this->setHelp($help);
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
            $allTypes = \Mage::app()->useCache();
            foreach(array_keys($allTypes) as $type) {
                if ($input->getArgument('type') == '' || $input->getArgument('type') == $type) {
                    \Mage::app()->getCacheInstance()->cleanType($type);
                    $output->writeln('<info>' . $type . ' cache cleaned</info>');
                }
            }
        }
    }
}