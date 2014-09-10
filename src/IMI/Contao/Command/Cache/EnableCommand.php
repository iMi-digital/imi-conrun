<?php

namespace IMI\Contao\Command\Cache;

use IMI\Contao\Application;
use IMI\Util\String;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnableCommand extends AbstractCacheCommand
{
    protected function configure()
    {
        $this
            ->setName('cache:enable')
            ->addArgument('code', InputArgument::OPTIONAL, 'Code of cache (Multiple codes sperated by comma)')
            ->setDescription('Enables contao caches')
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
            $this->saveCacheStatus($codeArgument, true);

            if (count($codeArgument) > 0) {
                foreach ($codeArgument as $code) {
                    $output->writeln('<info>Cache <comment>' . $code . '</comment> enabled</info>');
                }
            } else {
                $output->writeln('<info>Caches enabled</info>');
            }
        }
    }
}