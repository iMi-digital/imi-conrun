<?php

namespace IMI\Contao\Command\Script\Repository;

use IMI\Contao\Command\AbstractContaoCommand;

class AbstractRepositoryCommand extends AbstractContaoCommand
{
    /**
     * Extension of imi-conrun scripts
     */
    const MAGERUN_EXTENSION = '.conrun';

    /**
     * @return array
     */
    protected function getScripts()
    {
        $config = $this->getApplication()->getConfig();
        $loader = new ScriptLoader($config['script']['folders'], $this->getApplication()->getContaoRootFolder());
        $files = $loader->getFiles();

        return $files;
    }
}
