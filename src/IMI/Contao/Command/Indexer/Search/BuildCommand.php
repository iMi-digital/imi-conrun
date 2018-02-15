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

        $remote = false;

        if ($remote) {
            if ( ! $GLOBALS['TL_CONFIG']['enableSearch'] ) {
                $output->writeln( 'Search has to be enabled' );
                return;

            }
        } else {
            \Config::set('enableSearch', true);
        }

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );


        foreach ($searchablePages as $url) {
            if ($remote) {
                @file_get_contents( $url, false, stream_context_create( $arrContextOptions ) );
            } else {
                $urlParts = parse_url($url);
                \Environment::set('host', $urlParts['host']);
                \Environment::set('request', $urlParts['path']);
                // Run the controller
                $objPage = \Contao\FrontendIndex::getRootPageFromUrl();

                if (!$objPage->protected) {
                    $objPage->loadDetails();
                }

                $objHandler = new $GLOBALS['TL_PTY'][$objPage->type]();
                $objRootPage = \Contao\FrontendIndex::getRootPageFromUrl();

                $pageId = $objPage->id;
                // Generate the page
                switch ($objPage->type)
                {
                    case 'root':
                    case 'error_404':
                        break;
                    case 'error_403':
                        break;
                    default:
                        $objHandler->generate($objPage, true);
                        break;
                }

            }
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