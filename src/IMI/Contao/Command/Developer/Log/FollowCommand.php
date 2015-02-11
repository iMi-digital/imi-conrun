<?php

namespace IMI\Contao\Command\Developer\Log;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FollowCommand extends AbstractLogCommand
{
    protected function configure()
    {
        $this->setName('dev:log:follow')
             ->addArgument('n', InputArgument::OPTIONAL, 'Number of lines to print initially', 5)
             ->addOption('browser', 'b', InputOption::VALUE_NONE, 'Print browser agent')
             ->setDescription('Follow Database Log (tl_log)');
    }

    /**
     * @return string
     */
    protected function  _getVarienAdapterPhpFile()
    {
        return $this->_contaoRootFolder . '/lib/Varien/Db/Adapter/Pdo/Mysql.php';
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output);
        $this->initContao();

        $linesToPrint = (int) $name = $input->getArgument('n');

        if ($linesToPrint < 1) {
            $result = \Database::getInstance()->query("SELECT * FROM tl_log ORDER BY id DESC LIMIT 1");
            if ($result->numRows > 0) {
                $lastId = $result->id;
            } else {
                $lastId = 0;
            }
            $result = false;
        } else {
            $result = \Database::getInstance()->query("SELECT * FROM tl_log ORDER BY id DESC LIMIT $linesToPrint");
        }

        $browserFmt = $input->getOption('browser') ? ' [%s]' : '';

        while (true) {
            $buffer = array();

            if ($result !== false && $result->numRows > 0) {
                $lastId = 0;

                while($row = $result->fetchAssoc()) {
                    if ($lastId == 0) {
                        $lastId = (int) $result->id;
                    }
                    $buffer[] = sprintf('[%s] %s [%s %s %s] %s' . $browserFmt,
                        date('Y-m-d H:i:s', $result->tstamp),
                        $result->func,
                        $result->source, $result->ip, $result->username,
                        htmlspecialchars_decode($result->text),
                        $result->browser
                    );
                }
            }

            $buffer = array_reverse($buffer);
            foreach($buffer as $line) {
                $output->writeln($line);
            }

            usleep(250000); // quarter second
            $result = \Database::getInstance()->query("SELECT * FROM tl_log WHERE id > $lastId ORDER BY id DESC");
        }


    }

}