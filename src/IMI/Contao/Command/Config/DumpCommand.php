<?php

namespace IMI\Contao\Command\Config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends AbstractConfigCommand
{
    protected function configure()
    {
        $this
            ->setName('config:dump')
            ->addArgument('xpath', InputArgument::OPTIONAL, 'XPath to filter XML output', null)
            ->setDescription('Dump merged xml config')
        ;

        $help = <<<HELP
Dumps merged XML configuration to stdout. Useful to see all the XML.
You can filter the XML with first argument.

Examples:

  Config of catalog module

   $ imi-conrun.phar config:dump global/catalog

   See module order in XML

   $ imi-conrun.phar config:dump modules

   Write output to file

   $ imi-conrun.phar config:dump > extern_file.xml

HELP;
        $this->setHelp($help);

    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output, true);
        if ($this->initContao()) {
            $config = \Mage::app()->getConfig()->getNode($input->getArgument('xpath'));
            if (!$config) {
                throw new \InvalidArgumentException('xpath was not found');
            }
            $dom = new \DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($config->asXml());
            $output->writeln($dom->saveXML());
        }
    }
}
