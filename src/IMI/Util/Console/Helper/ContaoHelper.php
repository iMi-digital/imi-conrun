<?php

namespace IMI\Util\Console\Helper;

use IMI\Util\String;
use Symfony\Component\Console\Helper\Helper as AbstractHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputAwareInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class ContaoHelper extends AbstractHelper
{
    /**
     * @var string
     */
    protected $_contaoRootFolder = null;

    /**
     * @var string
     */
    protected $_contaoMajorVersion = \IMI\Contao\Application::MAGENTO_MAJOR_VERSION_1;

    /**
     * @var bool
     */
    protected $_contaoEnterprise = false;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'contao';
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function __construct(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }

        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Start Contao detection
     *
     * @param string $folder
     * @param array $subFolders Sub-folders to check
     */
    public function detect($folder, $subFolders = array())
    {
        $folders = $this->splitPathFolders($folder);
        $folders = $this->checkConrunFile($folders);
        $folders = $this->checkModman($folders);
        $folders = array_merge($folders, $subFolders);

        foreach (array_reverse($folders) as $searchFolder) {
            if (!is_dir($searchFolder) || !is_readable($searchFolder)) {
                continue;
            }
            if ($this->_search($searchFolder)) {
                break;
            }
        }
    }


    /**
     * @return string
     */
    public function getRootFolder()
    {
        return $this->_contaoRootFolder;
    }

    public function getEdition()
    {
        return $this->_contaoMajorVersion;
    }

    /**
     * @return bool
     */
    public function isEnterpriseEdition()
    {
        return $this->_contaoEnterprise;
    }

    /**
     * @return mixed
     */
    public function getMajorVersion()
    {
        return $this->_contaoMajorVersion;
    }

    /**
     * @param string $folder
     *
     * @return array
     */
    protected function splitPathFolders($folder)
    {
        $folders = array();

        $folderParts = explode(DIRECTORY_SEPARATOR, $folder);
        foreach ($folderParts as $key => $part) {
            $explodedFolder = implode(DIRECTORY_SEPARATOR, array_slice($folderParts, 0, $key + 1));
            if ($explodedFolder !== '') {
                $folders[] = $explodedFolder;
            }
        }

        return $folders;
    }

    /**
     * Check for modman file and .basedir
     *
     * @param array $folders
     *
     * @return array
     */
    protected function checkModman($folders)
    {
        foreach (array_reverse($folders) as $searchFolder) {
            if (!is_readable($searchFolder)) {
                if (OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity()) {
                    $this->output->writeln('<debug>Folder <info>' . $searchFolder . '</info> is not readable. Skip.</debug>');
                }
                continue;
            }

            $finder = Finder::create();
            $finder
                ->files()
                ->ignoreUnreadableDirs(true)
                ->depth(0)
                ->followLinks()
                ->ignoreDotFiles(false)
                ->name('.basedir')
                ->in($searchFolder);

            $count = $finder->count();
            if ($count > 0) {
                $baseFolderContent = trim(file_get_contents($searchFolder . DIRECTORY_SEPARATOR . '.basedir'));
                if (OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity()) {
                    $this->output->writeln('<debug>Found modman .basedir file with content <info>' . $baseFolderContent . '</info></debug>');
                }

                if (!empty($baseFolderContent)) {
                    $modmanBaseFolder = $searchFolder
                                      . DIRECTORY_SEPARATOR
                                      . '..'
                                      . DIRECTORY_SEPARATOR
                                      . $baseFolderContent;
                    array_push($folders, $modmanBaseFolder);
                }
            }
        }

        return $folders;
    }

    /**
     * Check for .imi-conrun file
     *
     * @param array $folders
     *
     * @return array
     */
    protected function checkConrunFile($folders)
    {
        foreach (array_reverse($folders) as $searchFolder) {
            if (!is_readable($searchFolder)) {
                if (OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity()) {
                    $this->output->writeln('<debug>Folder <info>' . $searchFolder . '</info> is not readable. Skip.</debug>');
                }
                continue;
            }
            $finder = Finder::create();
            $finder
                ->files()
                ->ignoreUnreadableDirs(true)
                ->depth(0)
                ->followLinks()
                ->ignoreDotFiles(false)
                ->name('.imi-conrun')
                ->in($searchFolder);

            $count = $finder->count();
            if ($count > 0) {
                $conrunFileContent = trim(file_get_contents($searchFolder . DIRECTORY_SEPARATOR . '.imi-conrun'));
                if (OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity()) {
                    $this->output->writeln('<debug>Found .imi-conrun file with content <info>' . $conrunFileContent . '</info></debug>');
                }

                $modmanBaseFolder = $searchFolder
                    . DIRECTORY_SEPARATOR
                    . $conrunFileContent;
                array_push($folders, $modmanBaseFolder);
            }
        }

        return $folders;
    }

    /**
     * @param string $searchFolder
     *
     * @return bool
     */
    protected function _search($searchFolder)
    {
        if (OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity()) {
            $this->output->writeln('<debug>Search for contao in folder <info>' . $searchFolder . '</info></debug>');
        }

        if (!is_dir($searchFolder . '/system')) {
            return false;
        }

        $finder = Finder::create();
        $finder
            ->ignoreUnreadableDirs(true)
            ->depth(0)
            ->followLinks()
            ->name('initialize.php')
            ->in($searchFolder . '/system');

        if ($finder->count() > 0) {
            $files = iterator_to_array($finder, false);
            /* @var $file \SplFileInfo */

            $this->_contaoRootFolder = $searchFolder;

            if (OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity()) {
                $this->output->writeln('<debug>Found Contao in folder <info>' . $this->_contaoRootFolder . '</info></debug>');
            }

            return true;
        }

        return false;
    }
}
