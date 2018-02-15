<?php

namespace IMI\Contao;

use IMI\Contao\Command\ConfigurationLoader;
use IMI\Contao\EntryPoint\Contrun as ContrunEntryPoint;
use IMI\Util\ArrayFunctions;
use IMI\Util\Console\Helper\TwigHelper;
use IMI\Util\Console\Helper\ContaoHelper;
use IMI\Util\OperatingSystem;
use IMI\Util\BinaryString;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Application extends BaseApplication
{
    /**
     * @var int
     */
    const MAGENTO_MAJOR_VERSION_1 = 1;
    /**
     * @var int
     */
    const MAGENTO_MAJOR_VERSION_2 = 2;
    /**
     * @var string
     */
    const APP_NAME = 'imi-conrun';
    /**
     * @var string
     */
    const APP_VERSION = '1.8.1';

    /**
     * @var string
     */
    private static $logo = '
 _ __  __ _
(_)  \/  (_)___ __ ___ _ _  _ _ _  _ _ _
| | |\/| | |___/ _/ _ \ \' \| \'_| || | \' \
|_|_|  |_|_|   \__\___/_||_|_|  \_,_|_||_|
';
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    protected $autoloader;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var ConfigurationLoader
     */
    protected $configurationLoader = null;

    /**
     * @var array
     */
    protected $partialConfig = array();

    /**
     * @var bool
     */
    protected $_contaoDetected = false;

    /**
     * @var string
     */
    protected $_contaoRootFolder = null;

    /**
     * @var bool
     */
    protected $_contaoEnterprise = false;

    /**
     * @var int
     */
    protected $_contaoMajorVersion = self::MAGENTO_MAJOR_VERSION_1;

    /**
     * @var EntryPoint
     */
    protected $_contao2EntryPoint = null;

    /**
     * @var bool
     */
    protected $_isPharMode = false;

    /**
     * @var bool
     */
    protected $_conrunStopFileFound = false;

    /**
     * @var string
     */
    protected $_conrunStopFileFolder = null;

    /**
     * @var bool
     */
    protected $_isInitialized = false;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * If root dir is set by root-dir option this flag is true
     *
     * @var bool
     */
    protected $_directRootDir = false;

    /**
     * @param \Composer\Autoload\ClassLoader $autoloader
     */
    public function __construct($autoloader = null)
    {
        $this->autoloader = $autoloader;
        parent::__construct(self::APP_NAME, self::APP_VERSION);
    }

    /**
     * @return \Symfony\Component\Console\Input\InputDefinition|void
     */
    protected function getDefaultInputDefinition()
    {
        $inputDefinition = parent::getDefaultInputDefinition();
        $rootDirOption = new InputOption(
            '--root-dir',
            '',
            InputOption::VALUE_OPTIONAL,
            'Force contao root dir. No auto detection'
        );
        $inputDefinition->addOption($rootDirOption);

        $skipExternalConfig = new InputOption(
            '--skip-config',
            '',
            InputOption::VALUE_OPTIONAL,
            'Do not load any custom config.'
        );
        $inputDefinition->addOption($skipExternalConfig);

        $skipExternalConfig = new InputOption(
            '--skip-root-check',
            '',
            InputOption::VALUE_OPTIONAL,
            'Do not check if imi-conrun runs as root'
        );
        $inputDefinition->addOption($skipExternalConfig);

        return $inputDefinition;
    }

    /**
     * Get names of sub-folders to be scanned during Contao detection
     * @return array
     */
    public function getDetectSubFolders()
    {
        if (isset($this->partialConfig['detect'])) {
            if (isset($this->partialConfig['detect']['subFolders'])) {
                return $this->partialConfig['detect']['subFolders'];
            }
        }
        return array();
    }

    /**
     * Search for contao root folder
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function detectContao(InputInterface $input = null, OutputInterface $output = null)
    {
        // do not detect magento twice
        if ($this->_contaoDetected) {
            return;
        }

        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }

        if ($this->getContaoRootFolder() === null) {
            $this->_checkRootDirOption($input);
            $folder = OperatingSystem::getCwd();
        } else {
            $folder = $this->getContaoRootFolder();
        }

        $this->getHelperSet()->set(new ContaoHelper($input, $output), 'contao');
        $contaoHelper = $this->getHelperSet()->get('contao');
        /* @var $contaoHelper ContaoHelper */
        if (!$this->_directRootDir) {
            $subFolders = $this->getDetectSubFolders();
        } else {
            $subFolders = array();
        }
        $contaoHelper->detect($folder, $subFolders);
        $this->_contaoRootFolder = $contaoHelper->getRootFolder();
        $this->_contaoEnterprise = $contaoHelper->isEnterpriseEdition();
        $this->_contaoMajorVersion = $contaoHelper->getMajorVersion();
    }

    /**
     * Add own helpers to helperset.
     *
     * @return void
     */
    protected function registerHelpers()
    {
        $helperSet = $this->getHelperSet();

        // Twig
        $twigBaseDirs = array(
            __DIR__ . '/../../../res/twig'
        );
        if (isset($this->config['twig']['baseDirs']) && is_array($this->config['twig']['baseDirs'])) {
            $twigBaseDirs = array_merge(array_reverse($this->config['twig']['baseDirs']), $twigBaseDirs);
        }
        $helperSet->set(new TwigHelper($twigBaseDirs), 'twig');

        foreach ($this->config['helpers'] as $helperName => $helperClass) {
            if (class_exists($helperClass)) {
                $helperSet->set(new $helperClass(), $helperName);
            }
        }
    }

    /**
     * Adds autoloader prefixes from user's config
     */
    protected function registerCustomAutoloaders()
    {
        if (isset($this->config['autoloaders']) && is_array($this->config['autoloaders'])) {
            foreach ($this->config['autoloaders'] as $prefix => $path) {
                $this->autoloader->add($prefix, $path);
            }
        }

        if (isset($this->config['autoloaders_psr4']) && is_array($this->config['autoloaders_psr4'])) {
            foreach ($this->config['autoloaders_psr4'] as $prefix => $path) {
                $this->autoloader->addPsr4($prefix, $path);
            }
        }
    }

    /**
     * @return bool
     */
    protected function hasCustomCommands()
    {
        return isset($this->config['commands']['customCommands'])
        && is_array($this->config['commands']['customCommands']);
    }

    /**
     * @return void
     */
    protected function registerCustomCommands()
    {
        if (!$this->hasCustomCommands()) {
            return;
        }

        foreach ($this->config['commands']['customCommands'] as $commandClass) {
            if (is_array($commandClass)) { // Support for key => value (name -> class)
                $resolvedCommandClass = current($commandClass);
                if ($this->isCommandDisabled($resolvedCommandClass)) {
                    continue;
                }
                $command = new $resolvedCommandClass();
                $command->setName(key($commandClass));
            } elseif ($this->isCommandDisabled($commandClass)) {
                continue;
            } else {
                $command = new $commandClass();
            }
            $this->add($command);
        }
    }

    /**
     * @param string $class
     * @return bool
     */
    protected function isCommandDisabled($class)
    {
        return in_array($class, $this->config['commands']['disabled']);
    }

    /**
     * Override standard command registration. We want alias support.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     * @return \Symfony\Component\Console\Command\Command
     */
    public function add(Command $command)
    {
        $this->registerConfigCommandAlias($command);

        return parent::add($command);
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     */
    protected function registerConfigCommandAlias(Command $command)
    {
        if ($this->hasConfigCommandAliases()) {
            foreach ($this->config['commands']['aliases'] as $alias) {
                if (!is_array($alias)) {
                    continue;
                }

                $aliasCommandName = key($alias);
                $commandString = $alias[$aliasCommandName];

                list($originalCommand) = explode(' ', $commandString);
                if ($command->getName() == $originalCommand) {
                    $currentCommandAliases = $command->getAliases();
                    $currentCommandAliases[] = $aliasCommandName;
                    $command->setAliases($currentCommandAliases);
                }
            }
        }
    }

    /**
     * @return bool
     */
    private function hasConfigCommandAliases()
    {
        return isset($this->config['commands']['aliases']) && is_array($this->config['commands']['aliases']);
    }

    /**
     * @param bool $mode
     */
    public function setPharMode($mode)
    {
        $this->_isPharMode = $mode;
    }

    /**
     * @return bool
     */
    public function isPharMode()
    {
        return $this->_isPharMode;
    }

    /**
     * @TODO Move logic into "EventSubscriber"
     *
     * @param OutputInterface $output
     * @return bool
     */
    public function checkVarDir(OutputInterface $output)
    {
        if (OutputInterface::VERBOSITY_NORMAL <= $output->getVerbosity()) {
            $tempVarDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'contao' . DIRECTORY_SEPARATOR . 'var';

            if (is_dir($tempVarDir)) {
                $this->detectContao(null, $output);
                /* If contao is not installed yet, don't check */
                if ($this->_contaoRootFolder === null
                    || !file_exists($this->_contaoRootFolder . '/app/etc/local.xml')
                ) {
                    return;
                }

                try {
                    $this->initContao();
                } catch (\Exception $e) {
                    $message = 'Cannot initialize Contao. Please check your configuration. '
                        . 'Some imi-conrun command will not work. Got message: ';
                    if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $output->getVerbosity()) {
                        $message .= $e->getTraceAsString();
                    } else {
                        $message .= $e->getMessage();
                    }
                    $output->writeln($message);

                    return;
                }

                $configOptions = new \Mage_Core_Model_Config_Options();
                $currentVarDir = $configOptions->getVarDir();

                if ($currentVarDir == $tempVarDir) {
                    $output->writeln(sprintf('<warning>Fallback folder %s is used in imi-conrun</warning>', $tempVarDir));
                    $output->writeln('');
                    $output->writeln('imi-conrun is using the fallback folder. If there is another folder configured for Contao, this can cause serious problems.');
                    $output->writeln('Please refer to https://github.com/netz98/imi-conrun/wiki/File-system-permissions for more information.');
                    $output->writeln('');
                } else {
                    $output->writeln(sprintf('<warning>Folder %s found, but not used in imi-conrun</warning>', $tempVarDir));
                    $output->writeln('');
                    $output->writeln(sprintf('This might cause serious problems. imi-conrun is using the configured var-folder <comment>%s</comment>', $currentVarDir));
                    $output->writeln('Please refer to https://github.com/netz98/imi-conrun/wiki/File-system-permissions for more information.');
                    $output->writeln('');

                    return false;
                }
            }
        }
    }

    public function initContao()
    {
        if ($this->getContaoRootFolder() !== null) {
            if ($this->_contaoMajorVersion == self::MAGENTO_MAJOR_VERSION_2) {
                $this->_initContao2();
            } else {
                $this->_initContao1();
            }

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return self::$logo . parent::getHelp();
    }

    public function getLongVersion()
    {
        return parent::getLongVersion() . ' by <info>iMi digital GmbH</info>' . PHP_EOL .
        'portions by <info>netz98 new media GmbH</info>';
    }

    /**
     * @return boolean
     */
    public function isContaoEnterprise()
    {
        return $this->_contaoEnterprise;
    }

    /**
     * @return string
     */
    public function getContaoRootFolder()
    {
        return $this->_contaoRootFolder;
    }

    /**
     * @param string $contaoRootFolder
     */
    public function setContaoRootFolder($contaoRootFolder)
    {
        $this->_contaoRootFolder = $contaoRootFolder;
    }

    /**
     * @return int
     */
    public function getContaoMajorVersion()
    {
        return $this->_contaoMajorVersion;
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public function getAutoloader()
    {
        return $this->autoloader;
    }

    /**
     * @param \Composer\Autoload\ClassLoader $autoloader
     */
    public function setAutoloader($autoloader)
    {
        $this->autoloader = $autoloader;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Runs the current application with possible command aliases
     *
     * @param InputInterface $input An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $event = new Application\Console\Event($this, $input, $output);
        $this->dispatcher->dispatch('imi-conrun.application.console.run.before', $event);

        /**
         * only for compatibility to old versions.
         */
        $event = new ConsoleEvent(new Command('dummy'), $input, $output);
        $this->dispatcher->dispatch('console.run.before', $event);

        $input = $this->checkConfigCommandAlias($input);
        if ($output instanceof ConsoleOutput) {
            $this->checkVarDir($output->getErrorOutput());
        }

        if (OutputInterface::VERBOSITY_DEBUG <= $output->getVerbosity()) {
            $output->writeln('DEBUG');
        }

        return parent::doRun($input, $output);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return \Symfony\Component\Console\Input\ArgvInput|\Symfony\Component\Console\Input\InputInterface
     */
    protected function checkConfigCommandAlias(InputInterface $input)
    {
        if ($this->hasConfigCommandAliases()) {
            foreach ($this->config['commands']['aliases'] as $alias) {
                if (is_array($alias)) {
                    $aliasCommandName = key($alias);
                    if ($input->getFirstArgument() == $aliasCommandName) {
                        $aliasCommandParams = array_slice(BinaryString::trimExplodeEmpty(' ', $alias[$aliasCommandName]), 1);
                        if (count($aliasCommandParams) > 0) {
                            // replace with aliased data
                            $mergedParams = array_merge(
                                array_slice($_SERVER['argv'], 0, 2),
                                $aliasCommandParams,
                                array_slice($_SERVER['argv'], 2)
                            );
                            $input = new ArgvInput($mergedParams);
                        }
                    }
                }
            }
            return $input;
        }
        return $input;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }
        $this->_addOutputStyles($output);
        if ($output instanceof ConsoleOutput) {
            $this->_addOutputStyles($output->getErrorOutput());
        }

        $this->configureIO($input, $output);

        try {
            $this->init(array(), $input, $output);
        } catch (\Exception $e) {
            $output = new ConsoleOutput();
            $this->renderException($e, $output);
        }

        $return = parent::run($input, $output);

        // Fix for no return values -> used in interactive shell to prevent error output
        if ($return === null) {
            return 0;
        }

        return $return;
    }

    /**
     * @param array $initConfig
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function init(array $initConfig = array(), InputInterface $input = null, OutputInterface $output = null)
    {
        if ($this->_isInitialized) {
            return;
        }

        // Suppress DateTime warnings
        date_default_timezone_set(@date_default_timezone_get());

        // Initialize EventDispatcher early
        $this->dispatcher = new EventDispatcher();
        $this->setDispatcher($this->dispatcher);

        if (null === $input) {
            $input = new ArgvInput();
        }

        if ($output === null) {
            $output = new NullOutput();
        }

        // initialize config
        $configLoader = $this->getConfigurationLoader($initConfig, $output);
        $loadExternalConfig = !$this->_checkSkipConfigOption($input);
        $this->partialConfig = $configLoader->getPartialConfig($loadExternalConfig);
        $this->detectContao($input, $output);
        $configLoader->loadStageTwo($this->_contaoRootFolder, $loadExternalConfig, $this->_conrunStopFileFolder);

        $this->config = $configLoader->toArray();

        if ($this->autoloader) {
            $this->registerCustomAutoloaders();
            $this->registerEventSubscribers();
            $this->registerCustomCommands();
        }

        $this->registerHelpers();

        $this->_isInitialized = true;
    }

    /**
     * @param array $initConfig
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public
    function reinit($initConfig = array(), InputInterface $input = null, OutputInterface $output = null)
    {
        $this->_isInitialized = false;
        $this->_contaoDetected = false;
        $this->_contaoRootFolder = null;
        $this->init($initConfig, $input, $output);
    }

    /**
     * @return void
     */
    protected
    function registerEventSubscribers()
    {
        foreach ($this->config['event']['subscriber'] as $subscriberClass) {
            $subscriber = new $subscriberClass();
            $this->dispatcher->addSubscriber($subscriber);
        }
    }

    /**
     * @param InputInterface $input
     * @return bool
     */
    protected
    function _checkSkipConfigOption(InputInterface $input)
    {
        return $input->hasParameterOption('--skip-config');
    }

    /**
     * @param InputInterface $input
     * @return string
     */
    protected
    function _checkRootDirOption(InputInterface $input)
    {
        $definedRootDir = $input->getParameterOption('--root-dir');

        if (!empty($definedRootDir)) {
            if ($definedRootDir[0] == '~') {
                $definedRootDir = OperatingSystem::getHomeDir() . substr($definedRootDir, 1);
            }

            $folder = realpath($definedRootDir);
            $this->_directRootDir = true;
            if (is_dir($folder)) {
                \chdir($folder);

                return;
            }
        }
    }

    /**
     * @return void
     */
    protected
    function _initContao2()
    {
        if ($this->_contao2EntryPoint === null) {
            require_once $this->getContaoRootFolder() . '/app/bootstrap.php';

            if (version_compare(\Mage::getVersion(), '2.0.0.0-dev42') >= 0) {
                $params = array(
                    \Mage::PARAM_RUN_CODE => 'admin',
                    \Mage::PARAM_RUN_TYPE => 'store',
                    'entryPoint' => basename(__FILE__),
                );
                try {
                    $this->_contao2EntryPoint = new ContrunEntryPoint(BP, $params);
                } catch (\Exception $e) {
                    // @TODO problem with objectmanager during tests. Find a better soluttion to reset object manager
                }
            } else {
                if (version_compare(\Mage::getVersion(), '2.0.0.0-dev41') >= 0) {
                    \Mage::app(array('MAGE_RUN_CODE' => 'admin'));
                } else {
                    \Mage::app('admin');
                }
            }
        }
    }

    /**
     * @return void
     */
    protected
    function _initContao1()
    {
        $initSettings = $this->config['init'];

        if (!class_exists('System')) {
            $autoloaders = spl_autoload_functions();

            define('TL_MODE', 'FE');

            require_once $this->getContaoRootFolder() . '/system/initialize.php';

            \System::loadLanguageFile('default');
            // Restore autoloaders that might be removed by extensions that overwrite Varien/Autoload
            //$this->_restoreAutoloaders($autoloaders);
        }
    }

    /**
     * @return void
     */
    protected
    function _restoreAutoloaders($loaders)
    {
        $current_loaders = spl_autoload_functions();
        foreach ($loaders as $function) {
            if (!in_array($function, $current_loaders)) {
                spl_autoload_register($function);
            }
        }
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     */
    public
    function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param array $initConfig
     * @param OutputInterface $output
     * @return ConfigurationLoader
     */
    public
    function getConfigurationLoader(array $initConfig = array(), OutputInterface $output)
    {
        if ($this->configurationLoader === null) {
            $this->configurationLoader = new ConfigurationLoader(
                ArrayFunctions::mergeArrays($this->config, $initConfig),
                $this->isPharMode(),
                $output
            );
        }

        return $this->configurationLoader;
    }

    /**
     * @param \IMI\Contao\Command\ConfigurationLoader $configurationLoader
     *
     * @return $this
     */
    public
    function setConfigurationLoader($configurationLoader)
    {
        $this->configurationLoader = $configurationLoader;

        return $this;
    }

    /**
     * @param OutputInterface $output
     */
    protected
    function _addOutputStyles(OutputInterface $output)
    {
        $output->getFormatter()->setStyle('debug', new OutputFormatterStyle('magenta', 'white'));
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('red', 'yellow', array('bold')));
    }
}
