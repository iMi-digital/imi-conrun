<?php

namespace IMI\Contao\System;


class Backend extends \Backend
{

    protected $_outputInterface;


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getOutputInterface()
    {
        return $this->_outputInterface;
    }

    /**
     * @param mixed $outputInterface
     */
    public function setOutputInterface($outputInterface)
    {
        $this->_outputInterface = $outputInterface;
    }


    /**
     * Execute runonce files
     *
     * @throws \Exception
     */
    public function runRunOnce()
    {
        $this->handleRunOnce();
    }

    /**
     * List runonce files
     *
     * @return array array of runonce files
     */
    public function listRunOnce()
    {
        $this->import('Files');
        $arrFiles = array('system/runonce.php');

        // Always scan all folders and not just the active modules (see #4200)
        foreach (scan(TL_ROOT . '/system/modules') as $strModule)
        {
            if (substr($strModule, 0, 1) == '.' || !is_dir(TL_ROOT . '/system/modules/' . $strModule))
            {
                continue;
            }

            $arrFiles[] = 'system/modules/' . $strModule . '/config/runonce.php';
        }

        $result = array();

        foreach($arrFiles as $strFile) {
            if (file_exists(TL_ROOT . '/' . $strFile)) {
                $result[] = $strFile;
            }

        }

        return $result;
    }
}