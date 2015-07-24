<?php

namespace IMI\Contao\System;

class PurgeData extends \PurgeData
{

    /**
     * Mostly Copied from
     * @see \Contao\PurgeData::run
     */
    public function getJobs()
    {
        \System::loadLanguageFile('default', 'en');
        \System::loadLanguageFile('tl_maintenance', 'en');

        $arrJobs = array();

        // Tables
        foreach ($GLOBALS['TL_PURGE']['tables'] as $key=>$config)
        {
            $arrJobs[$key] = array
            (
                'id' => 'purge_' . $key,
                'title' => $GLOBALS['TL_LANG']['tl_maintenance_jobs'][$key][0],
                'description' => $GLOBALS['TL_LANG']['tl_maintenance_jobs'][$key][1],
                'group' => 'tables',
                'affected' => ''
            );

            // Get the current table size
            foreach ($config['affected'] as $table)
            {
                $objCount = $this->Database->execute("SELECT COUNT(*) AS count FROM " . $table);

                // CHANGED
                $arrJobs[$key]['affected'] .= $table . ' ';
                $arrJobs[$key]['count'] = $objCount->count;
                $arrJobs[$key]['size'] = $this->getReadableSize($this->Database->getSizeOf($table), 0);
            }
        }

        // Folders
        foreach ($GLOBALS['TL_PURGE']['folders'] as $key=>$config)
        {
            $arrJobs[$key] = array
            (
                'id' => 'purge_' . $key,
                'title' => $GLOBALS['TL_LANG']['tl_maintenance_jobs'][$key][0],
                'description' => $GLOBALS['TL_LANG']['tl_maintenance_jobs'][$key][1],
                'group' => 'folders',
                'affected' => ''
            );

            // Get the current folder size
            foreach ($config['affected'] as $folder)
            {
                $total = 0;

                // Only check existing folders
                if (is_dir(TL_ROOT . '/' . $folder))
                {
                    // Recursively scan all subfolders
                    $objFiles = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator(
                            TL_ROOT . '/' . $folder,
                            \FilesystemIterator::UNIX_PATHS|\FilesystemIterator::FOLLOW_SYMLINKS|\FilesystemIterator::SKIP_DOTS
                        )
                    );

                    // Ignore .gitignore and index.html files
                    foreach ($objFiles as $objFile)
                    {
                        if ($objFile->getFilename() != '.gitignore' && $objFile->getFilename() != 'index.html')
                        {
                            ++$total;
                        }
                    }
                }

                $arrJobs[$key]['affected'] .=  $folder . ' ';
                $arrJobs[$key]['count'] = $total;
                $arrJobs[$key]['size'] = '';
            }
        }

        // Custom
        foreach ($GLOBALS['TL_PURGE']['custom'] as $key=>$job)
        {
            $arrJobs[$key] = array
            (
                'id' => 'purge_' . $key,
                'title' => $GLOBALS['TL_LANG']['tl_maintenance_jobs'][$key][0],
                'description' => $GLOBALS['TL_LANG']['tl_maintenance_jobs'][$key][1],
                'group' => 'custom',
                'affected' => '',
                'count' => '',
                'size' => '',
            );
        }

        return $arrJobs;
    }

    public function __construct()
    {
        parent::__construct();
    }
}