<?php

namespace IMI\Contao\Command\System\Store\Config;

use IMI\Contao\Command\AbstractContaoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use IMI\Util\Console\Helper\Table\Renderer\RendererFactory;

class BaseUrlListCommand extends AbstractContaoCommand
{
    protected function configure()
    {
        $this
            ->setName('sys:store:config:base-url:list')
            ->setDescription('Lists all base urls')
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

        if (!$input->getOption('format')) {
            $this->writeSection($output, 'Contao Stores - Base URLs');
        }
        $this->initContao();

        foreach (\Mage::app()->getStores() as $store) {
            $table[$store->getId()] = array(
                $store->getId(),
                $store->getCode(),
                \Mage::getStoreConfig('web/unsecure/base_url', $store),
                \Mage::getStoreConfig('web/secure/base_url', $store),
            );
        }

        ksort($table);
        $this->getHelper('table')
            ->setHeaders(array('id', 'code', 'unsecure_baseurl', 'secure_baseurl'))
            ->renderByFormat($output, $table, $input->getOption('format'));
    }
}
