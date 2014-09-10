<?php

namespace IMI\Contao\Command\Script\Repository;

use IMI\Util\OperatingSystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ScriptLoader
{
    /**
     * @var array
     */
    protected $_scriptFiles = array();

    /**
     * @var string
     */
    protected $_homeScriptFolder = '';

    /**
     * @var string
     */
    protected $_contaoRootFolder = '';

    /**
     * @var array
     */
    protected $_scriptFolders = array();

    /**
     * @param array  $scriptFolders
     * @param string $contaoRootFolder
     */
    public function __construct(array $scriptFolders, $contaoRootFolder = null)
    {
        $this->_contaoRootFolder = $contaoRootFolder;
        if (OperatingSystem::isWindows()) {
            $this->_homeScriptFolder = OperatingSystem::getHomeDir() . '/imi-conrun/scripts';
        } else {
            $this->_homeScriptFolder = OperatingSystem::getHomeDir() . '/.imi-conrun/scripts';
        }

        $this->_scriptFolders = $scriptFolders;
        $this->_scriptFolders[] = $this->_homeScriptFolder;
        foreach ($this->_scriptFolders as $key => $scriptFolder) {
            if (!is_dir($scriptFolder)) {
                unset($this->_scriptFolders[$key]);
            }
        }
        
        if (count($this->_scriptFolders)) {
            $this->findScripts();
        }
    }

    protected function findScripts()
    {
        $finder = Finder::create()
            ->files()
            ->followLinks(true)
            ->ignoreUnreadableDirs(true)
            ->name('*.conrun')
            ->in($this->_scriptFolders);

        $this->_scriptFiles = array();
        foreach ($finder as $file) { /* @var $file SplFileInfo */
            $this->_scriptFiles[$file->getFilename()] = array(
                'fileinfo'    => $file,
                'description' => $this->_readFirstLineOfFile($file->getPathname()),
                'location'    => $this->_getLocation($file->getPathname()),
            );
        }

        ksort($this->_scriptFiles);
    }

    /**
     * Reads the first line. If it's a comment return it.
     *
     * @param $file
     *
     * @return string
     */
    protected function _readFirstLineOfFile($file)
    {
        $f = @fopen($file, 'r');
        if (!$f) {
            return '';
        }
        $line = trim(fgets($f));
        fclose($f);

        if (isset($line[0]) && $line[0] != '#') {
            return '';
        }

        return trim(substr($line, 1));
    }

    /**
     * @param string $pathname
     *
     * @return string
     */
    protected function _getLocation($pathname)
    {
        if (strstr($pathname, $this->_contaoRootFolder)) {
            return 'project';
        }

        if (dirname($pathname) == $this->_homeScriptFolder) {
            return 'personal';
        }

        if (strstr($pathname, 'imi-conrun/modules')) {
            return 'module';
        }

        return 'system';
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->_scriptFiles;
    }
}
