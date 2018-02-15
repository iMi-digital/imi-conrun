<?php

namespace IMI\Contao\Command\Indexer\Search;

use IMI\Contao\Application;
use IMI\Contao\Command\AbstractContaoCommand;
use IMI\Contao\System\IndexerSearchBackend;
use IMI\Contao\System\PurgeData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends AbstractContaoCommand
{
    protected function configure()
    {
        $this
            ->setName('indexer:search:build')
            ->setDescription('Build search index');
    }




    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output, true);
        if (!$this->initContao()) {
            return;
        }

        $backend = new IndexerSearchBackend();
        $searchablePages = $backend->getSearchablePages();

        if (!$GLOBALS['TL_CONFIG']['enableSearch']) {
            $output->writeln('Search has to be enabled');
            die();
        }

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );

        foreach ($searchablePages as $url) {
            @file_get_contents($url, false, stream_context_create($arrContextOptions));
            $code = $http_response_header[0];
            if (strpos($code, ' 200 ') !== false) {
                $output->writeln("<info>$code</info> $url");
            } else {
                $output->writeln("<error>$code</error> $url");
            }
        }
        $output->writeln('<info>DCAs generated. You might want to clean / rebuild the DCA cache.</info>');
    }
}