<?php

namespace IMI\Contao\Command\Admin\User;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use IMI\Util\Console\Helper\Table\Renderer\RendererFactory;

class ListCommand extends AbstractAdminUserCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:user:list')
            ->setDescription('List admin users.')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output, true);
        if ($this->initContao()) {
            $userList = \UserModel::findAll();
            $table = array();
            foreach ($userList as $user) {
                $table[] = array(
                    $user->id,
                    $user->username,
                    $user->email,
                    ($user->locked == 0) ? 'open' : date('Y-m-d H:i:s', $user->locked),
                );
            }
            $this->getHelper('table')
                ->setHeaders(array('id', 'username', 'email', 'locked'))
                ->renderByFormat($output, $table, $input->getOption('format'));
        }
    }
}