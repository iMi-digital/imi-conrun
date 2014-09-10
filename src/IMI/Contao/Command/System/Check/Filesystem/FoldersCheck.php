<?php

namespace IMI\Contao\Command\System\Check\Filesystem;

use IMI\Contao\Command\CommandAware;
use IMI\Contao\Command\CommandConfigAware;
use IMI\Contao\Command\System\Check\Result;
use IMI\Contao\Command\System\Check\ResultCollection;
use IMI\Contao\Command\System\Check\SimpleCheck;
use IMI\Contao\Command\System\CheckCommand;
use Symfony\Component\Console\Command\Command;

class FoldersCheck implements SimpleCheck, CommandAware, CommandConfigAware
{
    /**
     * @var array
     */
    protected $_commandConfig;

    /**
     * @var CheckCommand
     */
    protected $_checkCommand;

    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results)
    {
        $folders = $this->_commandConfig['filesystem']['folders'];
        $contaoRoot = $this->_checkCommand->getApplication()->getContaoRootFolder();

        foreach ($folders as $folder => $comment) {
            $result = $results->createResult();
            if (file_exists($contaoRoot . DIRECTORY_SEPARATOR . $folder)) {
                $result->setStatus(Result::STATUS_OK);
                $result->setMessage("<info>Folder <comment>" . $folder . "</comment> found.</info>");
                if (!is_writeable($contaoRoot . DIRECTORY_SEPARATOR . $folder)) {
                    $result->setStatus(Result::STATUS_ERROR);
                    $result->setMessage("<error>Folder " . $folder . " is not writeable!</error><comment> Usage: " . $comment . "</comment>");
                }
            } else {
                $result->setStatus(Result::STATUS_ERROR);
                $result->setMessage("<error>Folder " . $folder . " not found!</error><comment> Usage: " . $comment . "</comment>");
            }
        }
    }

    /**
     * @param array $commandConfig
     */
    public function setCommandConfig(array $commandConfig)
    {
        $this->_commandConfig = $commandConfig;
    }

    /**
     * @param Command $command
     */
    public function setCommand(Command $command)
    {
        $this->_checkCommand = $command;
    }
}