<?php

namespace IMI\Contao\Command\Admin\User;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends AbstractAdminUserCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:user:create')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email, empty string = generate')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
            ->addArgument('name', InputArgument::OPTIONAL, 'Name')
            ->setDescription('Create admin user.')
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

            // Username
            if (($username = $input->getArgument('username')) === null) {
                $dialog = $this->getHelperSet()->get('dialog');
                $username = $dialog->ask($output, '<question>Username:</question>');
            }

            // Email
            if (($email = $input->getArgument('email')) === null) {
                $dialog = $this->getHelperSet()->get('dialog');
                $email = $dialog->ask($output, '<question>Email:</question>');
            }

            // Password
            if (($password = $input->getArgument('password')) === null) {
                $dialog = $this->getHelperSet()->get('dialog');
                $password = $dialog->ask($output, '<question>Password:</question>');
            }

            // Name
            if (($name = $input->getArgument('name')) === null) {
                $dialog = $this->getHelperSet()->get('dialog');
                $name = $dialog->ask($output, '<question>Name:</question>');
            }

            // create new user
            $user = new \UserModel();

            $user
                ->setRow(array(
                    'username' => $username,
                    'name' => $name,
                    'email' => $email,
                    'password' => \Encryption::hash($password),
                    'admin' => 1,
                ))->save();


            $user->save();

            $output->writeln('<info>User <comment>' . $username . '</comment> successfully created</info>');
        }
    }
}