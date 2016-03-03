<?php

namespace IMI\Contao\Command\System;

use IMI\Contao\Command\AbstractContaoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceCommand extends AbstractContaoCommand
{
    protected function configure()
    {
        $this
            ->setName('sys:maintenance')
            ->addOption('on', null, InputOption::VALUE_NONE, 'Force maintenance mode')
            ->addOption('off', null, InputOption::VALUE_NONE, 'Disable maintenance mode')
            ->addArgument('config-file', InputArgument::OPTIONAL, 'Config file name (default localconfig.php)')
            ->setDescription('Toggles maintenance mode.')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output);

        $configName = $input->getArgument('config-file');
        if (!$configName) {
            $configName = 'localconfig.php';

        }
        if ($input->getOption('off')) {
            $this->_switchOff($output, $configName);
        } elseif ($input->getOption('on')) {
            $this->_switchOn($output, $configName);
        } else {
            require $this->_contaoRootFolder . '/system/config/localconfig.php';

            if (isset($GLOBALS['TL_CONFIG']['maintenanceMode']) && $GLOBALS['TL_CONFIG']['maintenanceMode'] == true) {
                $this->_switchOff($output, $configName);
            } else {
                $this->_switchOn($output, $configName);
            }
        }
    }

    protected function setMaintenance($configName, $on = true)
    {
        $fileName = $this->_contaoRootFolder . '/system/config/' . $configName;
        $content = file_get_contents($fileName);

        $content = preg_replace("#^(\\\$GLOBALS\\['TL_CONFIG'\\]\\['maintenanceMode'\\][\\s]*=[\\s]*)(true|false)#im", "$1" . ($on ? 'true' : 'false'), $content, -1, $count);
        if ($count == 0) {
            $content .= PHP_EOL . "\$GLOBALS['TL_CONFIG']['maintenanceMode'] = " . ($on ? 'true' : 'false') . ";" . PHP_EOL;
        }

        file_put_contents($fileName, $content);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param $flagFile
     */
    protected function _switchOn(OutputInterface $output, $configName)
    {
        $this->setMaintenance($configName, true);
        $output->writeln('Maintenance mode <info>on</info>');
    }

    /**
     * @param OutputInterface $output
     * @param string $flagFile
     */
    protected function _switchOff(OutputInterface $output, $configName)
    {
        $this->setMaintenance($configName, false);
        $output->writeln('Maintenance mode <info>off</info>');
    }
}