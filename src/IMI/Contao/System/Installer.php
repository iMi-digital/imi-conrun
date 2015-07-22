<?php

namespace IMI\Contao\System;

class Installer extends \Database\Installer
{

    public function runUpdates($includingDrops = false)
    {
        $commands = $this->getSql($includingDrops);

        $this->import('Database');

        foreach($commands as $section) {

            foreach($section as $command)
            $this->Database->query(
                str_replace(
                    'DEFAULT CHARSET=utf8;',
                    'DEFAULT CHARSET=utf8 COLLATE ' . $GLOBALS['TL_CONFIG']['dbCollation'] . ';',
                    $command
                )
            );
        }
    }

    public function getSql($includingDrops = false)
    {
        $commands = $this->compileCommands();

        if (!$includingDrops) {
            unset($commands['DROP']);
            unset($commands['ALTER_DROP']);
        }

        return $commands;
    }
}