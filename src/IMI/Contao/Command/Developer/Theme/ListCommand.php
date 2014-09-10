<?php

namespace IMI\Contao\Command\Developer\Theme;

use IMI\Contao\Command\AbstractContaoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use IMI\Util\Console\Helper\Table\Renderer\RendererFactory;

class ListCommand extends AbstractContaoCommand
{
    protected function configure()
    {
        $this
            ->setName('dev:theme:list')
            ->setDescription('Lists all available themes')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output);
        if ($this->initContao()) {
            $packages = $this->getThemes();
            $table = array();
            foreach ($packages as $package => $themes) {
                foreach ($themes as $theme) {
                    $table[] = array(
                        ($package ? $package . '/' : '') . $theme
                    );
                }
            }

            $this->getHelper('table')
                ->setHeaders(array('Theme'))
                ->renderByFormat($output, $table, $input->getOption('format'));
        }
    }

    /**
     * @return array
     */
    protected function getThemes()
    {
        if ($this->_contaoMajorVersion == self::MAGENTO_MAJOR_VERSION_2) {
            $collection = \Mage::getModel('Mage_Core_Model_Theme')->getLabelsCollection();
            $themes = array();
            foreach ($collection as $theme) {
                $themes[] = $theme['label'];
            }

            return array($themes);
        }

        return \Mage::getModel('core/design_package')->getThemeList();
    }
}