<?php

namespace IMI\Contao\Command\System\Setup;

use IMI\Contao\Command\AbstractContaoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use IMI\Util\Console\Helper\Table\Renderer\RendererFactory;

/**
 * Class AbstractSetupCommand
 * @package IMI\Contao\Command\System\Setup
 */
class AbstractSetupCommand extends AbstractContaoCommand
{

    /**
     * @param string $moduleName
     * @return array
     */
    public function getModuleSetupResources($moduleName)
    {
        $moduleSetups   = array();
        $resources      = \Mage::getConfig()->getNode('global/resources')->children();

        foreach ($resources as $resName => $resource) {
            $modName = (string) $resource->setup->module;

            if ($modName == $moduleName) {
                $moduleSetups[$resName] = $resource;
            }
        }

        return $moduleSetups;
    }

    /**
     * @param InputInterface $input
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getModule(InputInterface $input)
    {
        $modules = \Mage::app()->getConfig()->getNode('modules')->asArray();

        foreach ($modules as $moduleName => $data) {
            if (strtolower($moduleName) === strtolower($input->getArgument('module'))) {
                return $moduleName;
            }
        }

        throw new \InvalidArgumentException(sprintf('No module found with name: "%s"', $input->getArgument('module')));
    }
}
