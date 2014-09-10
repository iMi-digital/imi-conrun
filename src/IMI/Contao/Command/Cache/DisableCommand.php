<?php

namespace IMI\Contao\Command\Cache;

use IMI\Contao\Application;
use IMI\Util\String;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableCommand extends AbstractCacheCommand
{
    protected function configure()
    {
        $this
            ->setName('cache:disable')
            ->addArgument('code', InputArgument::OPTIONAL, 'Code of cache (Multiple codes sperated by comma)')
            ->setDescription('Disables contao caches')
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
            $codeArgument = String::trimExplodeEmpty(',', $input->getArgument('code'));
            $this->saveCacheStatus($codeArgument, false);

            if (empty($codeArgument)) {
                $this->_getCacheModel()->flush();
            } else {
                foreach ($codeArgument as $type) {
                    $this->_getCacheModel()->cleanType($type);
                }
            }

            if (count($codeArgument) > 0) {
                foreach ($codeArgument as $code) {
                    $output->writeln('<info>Cache <comment>' . $code . '</comment> disabled</info>');
                }
            } else {
                $output->writeln('<info>Caches disabled</info>');
            }
        }
    }
}