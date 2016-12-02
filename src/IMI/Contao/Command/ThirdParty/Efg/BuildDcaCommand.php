<?php

namespace IMI\Contao\Command\ThirdParty\Efg;

use IMI\Contao\Application;
use IMI\Contao\Command\AbstractContaoCommand;
use IMI\Contao\System\PurgeData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildDcaCommand extends AbstractContaoCommand
{
    protected function configure()
    {
        $this
            ->setName('thirdparty:efg:build-dca')
            ->setDescription('Clean all cache folders');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output, true);
        if (!$this->initContao()) {
            return;
        }
        if (!class_exists('\Efg\FormdataBackend')) {
            throw new \ErrorException('EFG not present in the project');
        }
        $backend = new \Efg\FormdataBackend();
        $allForms = \FormModel::findAll();
        foreach ($allForms as $form) {
            $arrForm = $form->row();
            $strFormKey = (!empty($arrForm['alias'])) ? $arrForm['alias'] : str_replace('-', '_', standardize($arrForm['title']));
            $backend->updateConfig(array($strFormKey => $arrForm));
        }
        $output->writeln('<info>DCAs generated. You might want to clean / rebuild the DCA cache.</info>');
    }
}