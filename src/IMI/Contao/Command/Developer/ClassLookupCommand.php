<?php

namespace IMI\Contao\Command\Developer;

use IMI\Contao\Command\AbstractContaoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClassLookupCommand extends AbstractContaoCommand
{
    protected function configure()
    {
        $this
            ->setName('dev:class:lookup')
            ->setDescription('Resolves a grouped class name')
            ->addArgument('type', InputArgument::REQUIRED, 'The type of the class (helper|block|model)')
            ->addArgument('name', InputArgument::REQUIRED, 'The grouped class name')
        ;
    }

    /**
     * @return \Mage_Core_Model_Config
     */
    protected function _getConfig()
    {
        return \Mage::getConfig();
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
            $resolved = $this->_getConfig()->getGroupedClassName($input->getArgument('type'), $input->getArgument('name'));
            $output->writeln(ucfirst($input->getArgument('type')) . ' <comment>' . $input->getArgument('name') . "</comment> resolves to <comment>" . $resolved . '</comment>');
        }
    }
}
