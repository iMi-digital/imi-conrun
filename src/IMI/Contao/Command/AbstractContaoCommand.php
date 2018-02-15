<?php

namespace IMI\Contao\Command;

use Composer\Package\PackageInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Package\Loader\ArrayLoader as PackageLoader;
use Composer\Factory as ComposerFactory;
use Composer\IO\ConsoleIO;
use IMI\Util\Console\Helper\ContaoHelper;

/**
 * Class AbstractContaoCommand
 *
 * @package IMI\Contao\Command
 *
 * @method \IMI\Contao\Application getApplication() getApplication()
 */
abstract class AbstractContaoCommand extends Command
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
    protected $_contaoRootFolder = null;

    /**
     * @var int
     */
    protected $_contaoMajorVersion = self::MAGENTO_MAJOR_VERSION_1;

    /**
     * @var bool
     */
    protected $_contaoEnterprise = false;

    /**
     * @var array
     */
    protected $_deprecatedAlias = array();

    /**
     * @var array
     */
    protected $_websiteCodeMap = array();

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->checkDeprecatedAliases($input, $output);
    }

    /**
     * @param array $codeArgument
     * @param bool  $status
     * @return bool
     */
    protected function saveCacheStatus($codeArgument, $status)
    {
        $cacheTypes = $this->_getCacheModel()->getTypes();
        $enable = \Mage::app()->useCache();
        foreach ($cacheTypes as $cacheCode => $cacheModel) {
            if (empty($codeArgument) || in_array($cacheCode, $codeArgument)) {
                $enable[$cacheCode] = $status ? 1 : 0;
            }
        }

        \Mage::app()->saveUseCache($enable);
    }

    private function _initWebsites()
    {
        $this->_websiteCodeMap = array();
        /** @var \Mage_Core_Model_Website[] $websites */
        $websites = \Mage::app()->getWebsites(false);
        foreach ($websites as $website) {
            $this->_websiteCodeMap[$website->getId()] = $website->getCode();
        }
    }

    /**
     * @param int $websiteId
     * @return string
     */
    protected function _getWebsiteCodeById($websiteId)
    {
        if (empty($this->_websiteCodeMap)) {
            $this->_initWebsites();
        }

        if (isset($this->_websiteCodeMap[$websiteId])) {
            return $this->_websiteCodeMap[$websiteId];
        }

        return '';
    }

    /**
     * @param string $websiteCode
     * @return int
     */
    protected function _getWebsiteIdByCode($websiteCode)
    {
        if (empty($this->_websiteCodeMap)) {
            $this->_initWebsites();
        }
        $websiteMap = array_flip($this->_websiteCodeMap);

        return $websiteMap[$websiteCode];
    }

    /**
     * @param string|null $commandClass
     * @return array
     */
    protected function getCommandConfig($commandClass = null)
    {
        if ($commandClass == null) {
            $commandClass = get_class($this);
        }
        $configArray = $this->getApplication()->getConfig();
        if (isset($configArray['commands'][$commandClass])) {
            return $configArray['commands'][$commandClass];
        }

        return null;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $text
     * @param string $style
     */
    protected function writeSection(OutputInterface $output, $text, $style = 'bg=blue;fg=white')
    {
        $output->writeln(array(
            '',
            $this->getHelperSet()->get('formatter')->formatBlock($text, $style, true),
            '',
        ));
    }

    /**
     * Bootstrap contao shop
     *
     * @return bool
     */
    protected function initContao()
    {
        $init = $this->getApplication()->initContao();
        if ($init) {
            $this->_contaoRootFolder = $this->getApplication()->getContaoRootFolder();
        }

        return $init;
    }

    protected function initLang()
    {

    }

    /**
     * Search for contao root folder
     *
     * @param OutputInterface $output
     * @param bool $silent print debug messages
     * @throws \RuntimeException
     */
    public function detectContao(OutputInterface $output, $silent = true)
    {
        $this->getApplication()->detectContao();

        $this->_contaoEnterprise = $this->getApplication()->isContaoEnterprise();
        $this->_contaoRootFolder = $this->getApplication()->getContaoRootFolder();
        $this->_contaoMajorVersion = $this->getApplication()->getContaoMajorVersion();

        if (!$silent) {
            $editionString = ($this->_contaoEnterprise ? ' (Enterprise Edition) ' : '');
            $output->writeln('<info>Found Contao '. $editionString . 'in folder "' . $this->_contaoRootFolder . '"</info>');
        }

        if (!empty($this->_contaoRootFolder)) {
            return;
        }

        throw new \RuntimeException('Contao folder could not be detected');
    }

    /**
     * Die if not Enterprise
     */
    protected function requireEnterprise(OutputInterface $output)
    {
        if (!$this->_contaoEnterprise) {
            $output->writeln('<error>Enterprise Edition is required but was not detected</error>');
            exit;
        }
    }

    /**
     * @return \Mage_Core_Helper_Data
     */
    protected function getCoreHelper()
    {
        if ($this->_contaoMajorVersion == self::MAGENTO_MAJOR_VERSION_2) {
            return \Mage::helper('Mage_Core_Helper_Data');
        }
        return \Mage::helper('core');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return \Composer\Downloader\DownloadManager
     */
    protected function getComposerDownloadManager($input, $output)
    {
        return $this->getComposer($input, $output)->getDownloadManager();
    }

    /**
     * @param array|PackageInterface $config
     * @return \Composer\Package\CompletePackage
     */
    protected function createComposerPackageByConfig($config)
    {
        $packageLoader = new PackageLoader();
        return $packageLoader->load($config);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array|PackageInterface $config
     * @param string $targetFolder
     * @param bool $preferSource
     * @return \Composer\Package\CompletePackage
     */
    protected function downloadByComposerConfig(InputInterface $input, OutputInterface $output, $config, $targetFolder,
        $preferSource = true
    ) {
        $dm = $this->getComposerDownloadManager($input, $output);
        if (! $config instanceof PackageInterface) {
            $package = $this->createComposerPackageByConfig($config);
        } else {
            $package = $config;
        }

        $helper = new \IMI\Util\Console\Helper\ContaoHelper();
        $helper->detect($targetFolder);
        if ($this->isSourceTypeRepository($package->getSourceType()) && $helper->getRootFolder() == $targetFolder) {
            $package->setInstallationSource('source');
            $this->checkRepository($package, $targetFolder);
            $dm->update($package, $package, $targetFolder);
        } else {
            $dm->download($package, $targetFolder, $preferSource);
        }

        return $package;
    }

    /**
     * brings locally cached repository up to date if it is missing the requested tag
     *
     * @param $package
     * @param $targetFolder
     */
    protected function checkRepository($package, $targetFolder)
    {
        if ($package->getSourceType() == 'git') {
            $command = sprintf(
                'cd %s && git rev-parse refs/tags/%s',
                escapeshellarg($targetFolder),
                escapeshellarg($package->getSourceReference())
            );
            $existingTags = shell_exec($command);
            if (!$existingTags) {
                $command = sprintf('cd %s && git fetch', escapeshellarg($targetFolder));
                shell_exec($command);
            }
        } elseif ($package->getSourceType() == 'hg') {
            $command = sprintf(
                'cd %s && hg log --template "{tags}" -r %s',
                escapeshellarg($targetFolder),
                escapeshellarg($package->getSourceReference())
            );
            $existingTag =  shell_exec($command);
            if ($existingTag === $package->getSourceReference()) {
                $command = sprintf('cd %s && hg pull', escapeshellarg($targetFolder));
                shell_exec($command);
            }
        }
    }

    /**
     * obtain composer
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return \Composer\Composer
     */
    protected function getComposer(InputInterface $input, OutputInterface $output)
    {
        $io = new ConsoleIO($input, $output, $this->getHelperSet());
        return ComposerFactory::create($io, array());
    }

    /**
     * @param string $alias
     * @param string $message
     * @return AbstractContaoCommand
     */
    protected function addDeprecatedAlias($alias, $message)
    {
        $this->_deprecatedAlias[$alias] = $message;

        return $this;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function checkDeprecatedAliases(InputInterface $input, OutputInterface $output)
    {
        if (isset($this->_deprecatedAlias[$input->getArgument('command')])) {
            $output->writeln('<error>Deprecated:</error> <comment>' . $this->_deprecatedAlias[$input->getArgument('command')] . '</comment>');
        }
    }

    /**
     * Contao 1 / 2 switches
     *
     * @param string $mage1code Contao 1 class code
     * @param string $mage2class Contao 2 class name
     * @return \Mage_Core_Model_Abstract
     */
    protected function _getModel($mage1code, $mage2class)
    {
        if ($this->_contaoMajorVersion == self::MAGENTO_MAJOR_VERSION_2) {
            return \Mage::getModel($mage2class);
        } else {
            return \Mage::getModel($mage1code);
        }
    }

    /**
     * Contao 1 / 2 switches
     *
     * @param string $mage1code Contao 1 class code
     * @param string $mage2class Contao 2 class name
     * @return \Mage_Core_Model_Abstract
     */
    protected function _getResourceModel($mage1code, $mage2class)
    {
        if ($this->_contaoMajorVersion == self::MAGENTO_MAJOR_VERSION_2) {
            return \Mage::getResourceModel($mage2class);
        } else {
            return \Mage::getResourceModel($mage1code);
        }
    }

    /**
     * Contao 1 / 2 switches
     *
     * @param string $mage1code Contao 1 class code
     * @param string $mage2class Contao 2 class name
     * @return \Mage_Core_Model_Abstract
     */
    protected function _getResourceSingleton($mage1code, $mage2class)
    {
        if ($this->_contaoMajorVersion == self::MAGENTO_MAJOR_VERSION_2) {
            return \Mage::getResourceSingleton($mage2class);
        } else {
            return \Mage::getResourceSingleton($mage1code);
        }
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function _parseBoolOption($value)
    {
        return in_array(strtolower($value), array('y', 'yes', 1, 'true'));
    }

    /**
     * @param string $value
     * @return string
     */
    protected function formatActive($value)
    {
        if (in_array($value, array(1, 'true'))) {
            return 'active';
        }

        return 'inactive';
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->getHelperSet()->setCommand($this);

        return parent::run($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function chooseInstallationFolder(InputInterface $input, OutputInterface $output)
    {
        $validateInstallationFolder = function($folderName) use ($input) {

            $folderName = rtrim(trim($folderName, ' '), '/');
            if (substr($folderName, 0, 1) == '.') {
                $cwd = \getcwd() ;
                if (empty($cwd) && isset($_SERVER['PWD'])) {
                    $cwd = $_SERVER['PWD'];
                }
                $folderName = $cwd . substr($folderName, 1);
            }

            if (empty($folderName)) {
                throw new \InvalidArgumentException('Installation folder cannot be empty');
            }

            if (!is_dir($folderName)) {
                if (!@mkdir($folderName,0777, true)) {
                    throw new \InvalidArgumentException('Cannot create folder.');
                }

                return $folderName;
            }

            if ($input->hasOption('noDownload') && $input->getOption('noDownload')) {
                /** @var ContaoHelper $contaoHelper */
                $contaoHelper = new ContaoHelper();
                $contaoHelper->detect($folderName);
                if ($contaoHelper->getRootFolder() !== $folderName) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Folder %s is not a Contao working copy.',
                            $folderName
                        )
                    );
                }

                $localXml = $folderName . '/app/etc/local.xml';
                if (file_exists($localXml)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Contao working copy in %s seems already installed. Please remove %s and retry.',
                            $folderName,
                            $localXml
                        )
                    );
                }
            }

            return $folderName;
        };

        if (($installationFolder = $input->getOption('installationFolder')) == null) {
            $defaultFolder = './contao';
            $question[] = "<question>Enter installation folder:</question> [<comment>" . $defaultFolder . "</comment>]";

            $installationFolder = $this->getHelper('dialog')->askAndValidate($output, $question, $validateInstallationFolder, false, $defaultFolder);

        } else {
            // @Todo improve validation and bring it to 1 single function
            $installationFolder = $validateInstallationFolder($installationFolder);

        }

        $this->config['installationFolder'] = realpath($installationFolder);
        \chdir($this->config['installationFolder']);
    }

    protected function isSourceTypeRepository($type)
    {
        return in_array($type, array('git', 'hg'));
    }

    protected function getClass($strClass)
    {
        return (in_array('getInstance', get_class_methods($strClass))) ? call_user_func(array($strClass, 'getInstance')) : new $strClass();
    }

}
