<?php

namespace IMI\Contao\Command\Admin\User;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangePasswordCommand extends AbstractAdminUserCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:user:change-password')
            ->addArgument('id', InputArgument::OPTIONAL, 'Username or Email')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
            ->setDescription('Changes the password of a adminhtml user.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectContao($output);
        if ($this->initContao()) {
            
            $dialog = $this->getHelperSet()->get('dialog');
            
            if (($id = $input->getArgument('id')) == null) {
                $id = $dialog->ask($output, '<question>Username or Email:</question>');
            }

            $user = \UserModel::findBy('username', $id);
            if (!$user) {
                $user = \UserModel::findBy('email', $id);
            }

            if (!$user) {
                $output->writeln('<error>User was not found</error>');
                return;
            }

            // Password
            if (($password = $input->getArgument('password')) == null) {
                $password = $dialog->ask($output, '<question>Password:</question>');
            }

            try {
                $user->password = \Encryption::hash($password);
                $user->save();
                $output->writeln('<info>Password successfully changed</info>');
            } catch (\Exception $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
        }
    }
}