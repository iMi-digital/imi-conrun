<?php

namespace IMI\Contao\Command;

use FSHL\Output;
use IMI\Util\String;
use IMI\Util\OperatingSystem;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use IMI\Util\ArrayFunctions;

/**
 * Config consists of several parts which are merged.
 * The configuration which is global (not Contao project specific) is loaded
 * during construction.
 *
 * As soon as the Contao folder is known, loadStageTwo should be called.
 *
 * The toArray method only works if the Contao folder specific configuration is already loaded.
 *
 * Class ConfigurationLoader
 * @package IMI\Contao\Command
 */
class ConfigurationLoader
{
    /**
     * Config passed in the constructor
     *
     * @var array
     */
    protected $_initialConfig;

    /**
     * @var array
     */
    protected $_configArray = null;

    /**
     * Cache
     *
     * @var array
     */
    protected $_distConfig;

    /**
     * Cache
     *
     * @var array
     */
    protected $_pluginConfig;

    /**
     * Cache
     *
     * @var array
     */
    protected $_systemConfig;

    /**
     * Cache
     *
     * @var array
     */
    protected $_userConfig;

    /**
     * Cache
     *
     * @var array
     */
    protected $_projectConfig;

    /**
     * @var string
     */
    protected $_customConfigFilename = 'imi-conrun.yaml';

    /**
     * @var bool
     */
    protected $_isPharMode = true;

    /**
     * @var OutputInterface
     */
    protected $_output;

    /**
     * Load config
     * If $contaoRootFolder is null, only non-project config is loaded
     *
     * @param array $config
     * @param bool $isPharMode
     * @param OutputInterface $output
     */
    public function __construct($config, $isPharMode, OutputInterface $output)
    {
        $this->_initialConfig = $config;
        $this->_isPharMode = $isPharMode;
        $this->_output = $output;
    }

    /**
     * @param bool $loadExternalConfig
     * @return array
     */
    public function getPartialConfig($loadExternalConfig = true)
    {
        $config = $this->_initialConfig;
        $config = $this->loadDistConfig($config);
        if ($loadExternalConfig) {
            $config = $this->loadSystemConfig($config);
            $config = $this->loadUserConfig($config);
        }

        return $config;
    }

    /**
     * @param string $contaoRootFolder
     * @param bool   $loadExternalConfig
     */
    public function loadStageTwo($contaoRootFolder, $loadExternalConfig = true)
    {
        $config = $this->_initialConfig;
        $config = $this->loadDistConfig($config);
        if ($loadExternalConfig) {
            $config = $this->loadPluginConfig($config, $contaoRootFolder);
            $config = $this->loadSystemConfig($config);
            $config = $this->loadUserConfig($config, $contaoRootFolder);
            $config = $this->loadProjectConfig($contaoRootFolder, $config);
        }
        $this->_configArray = $config;
    }

    /**
     * @throws \ErrorException
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->_configArray == null) {
            throw new \ErrorException('Configuration not yet fully loaded');
        }

        return $this->_configArray;
    }

    /**
     * @param array $initConfig
     *
     * @return array
     */
    protected function loadDistConfig($initConfig)
    {
        if ($this->_distConfig == null) {
            $this->_distConfig = Yaml::parse(__DIR__ . '/../../../../config.yaml');
        }
        if (OutputInterface::VERBOSITY_DEBUG <= $this->_output->getVerbosity()) {
            $this->_output->writeln('<debug>Load dist config</debug>');
        }
        $config = ArrayFunctions::mergeArrays($this->_distConfig, $initConfig);

        return $config;
    }

    /**
     * Check if there is a global config file in /etc folder
     *
     * @param array $config
     *
     * @return array
     */
    public function loadSystemConfig($config)
    {
        if ($this->_systemConfig == null) {
            if (OperatingSystem::isWindows()) {
                $systemWideConfigFile = getenv('WINDIR') . DIRECTORY_SEPARATOR . $this->_customConfigFilename;
            } else {
                $systemWideConfigFile = '/etc/' . $this->_customConfigFilename;
            }

            if ($systemWideConfigFile && file_exists($systemWideConfigFile)) {
                if (OutputInterface::VERBOSITY_DEBUG <= $this->_output->getVerbosity()) {
                    $this->_output->writeln('<debug>Load system config <comment>' . $systemWideConfigFile . '</comment></debug>');
                }
                $this->_systemConfig = Yaml::parse($systemWideConfigFile);
            } else {
                $this->_systemConfig = array();
            }
        }

        $config = ArrayFunctions::mergeArrays($config, $this->_systemConfig);
        return $config;
    }

    /**
     * Load config from all installed bundles
     *
     * @param array  $config
     * @param string $contaoRootFolder
     *
     * @return array
     */
    public function loadPluginConfig($config, $contaoRootFolder)
    {
        if ($this->_pluginConfig == null) {
            $this->_pluginConfig = array();
            $moduleBaseFolders = array();
            if (OperatingSystem::isWindows()) {
                $config['plugin']['folders'][] = getenv('WINDIR') . '/imi-conrun/modules';
                $config['plugin']['folders'][] = OperatingSystem::getHomeDir() . '/imi-conrun/modules';
            } else {
                $config['plugin']['folders'][] = OperatingSystem::getHomeDir() . '/.imi-conrun/modules';
            }
            $config['plugin']['folders'][] = $contaoRootFolder . '/lib/imi-conrun/modules';
            foreach ($config['plugin']['folders'] as $folder) {
                if (is_dir($folder)) {
                    $moduleBaseFolders[] = $folder;
                }
            }

            /**
             * Allow modules to be placed vendor folder if not in phar mode
             */
            if (!$this->_isPharMode) {
                if (is_dir($this->getVendorDir())) {
                    $finder = Finder::create();
                    $finder
                        ->files()
                        ->depth(2)
                        ->followLinks()
                        ->ignoreUnreadableDirs(true)
                        ->name('imi-conrun.yaml')
                        ->in($this->getVendorDir());

                    foreach ($finder as $file) { /* @var $file \Symfony\Component\Finder\SplFileInfo */
                        $this->registerPluginConfigFile($contaoRootFolder, $file);
                    }
                }
            }

            if (count($moduleBaseFolders) > 0) {
                // Glob plugin folders
                $finder = Finder::create();
                $finder
                    ->files()
                    ->depth(1)
                    ->followLinks()
                    ->ignoreUnreadableDirs(true)
                    ->name('imi-conrun.yaml')
                    ->in($moduleBaseFolders);

                foreach ($finder as $file) { /* @var $file \Symfony\Component\Finder\SplFileInfo */
                    $this->registerPluginConfigFile($contaoRootFolder, $file);
                }
            }
        }

        $config = ArrayFunctions::mergeArrays($config, $this->_pluginConfig);

        return $config;
    }

    /**
     * @param string                                $rawConfig
     * @param string                                $contaoRootFolder
     * @param \Symfony\Component\Finder\SplFileInfo $file
     *
     * @return string
     */
    protected function applyVariables($rawConfig, $contaoRootFolder, $file = null)
    {
        $replace = array(
            '%module%' => $file ? $file->getPath() : '',
            '%root%'   => $contaoRootFolder,
        );

        return str_replace(array_keys($replace), $replace, $rawConfig);
    }


    /**
     * Check if there is a user config file. ~/.imi-conrun.yaml
     *
     * @param array  $config
     * @param string $contaoRootFolder
     *
     * @return array
     */
    public function loadUserConfig($config, $contaoRootFolder = null)
    {
        if ($this->_userConfig == null) {
            $this->_userConfig = array();
            $homeDirectory =  OperatingSystem::getHomeDir();
            if (OperatingSystem::isWindows()) {
                $personalConfigFile = $homeDirectory . DIRECTORY_SEPARATOR . $this->_customConfigFilename;
            } else {
                $personalConfigFile = $homeDirectory . DIRECTORY_SEPARATOR . '.' . $this->_customConfigFilename;
            }

            if ($homeDirectory && file_exists($personalConfigFile)) {
                $userConfig = $this->applyVariables(\file_get_contents($personalConfigFile), $contaoRootFolder, null);
                $this->_userConfig = Yaml::parse($userConfig);

                return $config;
            }
        }

        $config = ArrayFunctions::mergeArrays($config, $this->_userConfig);

        return $config;
    }

    /**
     * MAGENTO_ROOT/app/etc/imi-conrun.yaml
     *
     * @param string $contaoRootFolder
     * @param array $config
     *
     * @return array
     */
    public function loadProjectConfig($contaoRootFolder, $config)
    {
        if ($this->_projectConfig == null) {
            $this->_projectConfig = array();
            $projectConfigFile = $contaoRootFolder . DIRECTORY_SEPARATOR . 'app/etc/' . $this->_customConfigFilename;
            if ($projectConfigFile && file_exists($projectConfigFile)) {
                $projectConfig = $this->applyVariables(\file_get_contents($projectConfigFile), $contaoRootFolder, null);
                $this->_projectConfig = Yaml::parse($projectConfig);
            }

            $config = ArrayFunctions::mergeArrays($config, $this->_projectConfig);
        }

        return $config;
    }

    /**
     * Loads a plugin config file and merges it to plugin config
     *
     * @param string       $contaoRootFolder
     * @param SplFileInfo $file
     */
    protected function registerPluginConfigFile($contaoRootFolder, $file)
    {
        if (String::startsWith($file->getPathname(), 'vfs://')) {
            $path = $file->getPathname();
        } else {
            $path = $file->getRealPath();
        }

        if (OutputInterface::VERBOSITY_DEBUG <= $this->_output->getVerbosity()) {
            $this->_output->writeln('<debug>Load plugin config <comment>' . $path . '</comment></debug>');
        }

        $localPluginConfig = \file_get_contents($path);
        $localPluginConfig = Yaml::parse($this->applyVariables($localPluginConfig, $contaoRootFolder, $file));

        $this->_pluginConfig = ArrayFunctions::mergeArrays($this->_pluginConfig, $localPluginConfig);
    }

    /**
     * @return string
     */
    public function getVendorDir()
    {
        /* old vendor folder to give backward compatibility */
        $vendorFolder = $this->getConfigurationLoaderDir() . '/../../../../vendor';
        if (is_dir($vendorFolder)) {
            return $vendorFolder;
        }

        /* correct vendor folder for composer installations */
        $vendorFolder = $this->getConfigurationLoaderDir() . '/../../../../../../../vendor';
        if (is_dir($vendorFolder)) {
            return $vendorFolder;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getConfigurationLoaderDir()
    {
        return __DIR__;
    }
}