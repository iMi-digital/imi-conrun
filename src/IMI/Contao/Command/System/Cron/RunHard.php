<?php

namespace IMI\Contao\Command\System\Cron;

use IMI\Contao\Command\AbstractContaoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class RunHardCommand extends AbstractCronCommand
{
    /**
     * @var array
     */
    protected $infos;

    protected function configure()
    {
        $this
            ->setName('sys:cron:run:hard')
            ->setDescription('Runs all cronjobs by truncating tl_cron and calling the cron runner (the hard way)');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output, true);
        if ($this->initContao()) {
            $this->
            // Run the controller
            $controller = new FrontendCron;
            $controller->run();
        }
    }

}