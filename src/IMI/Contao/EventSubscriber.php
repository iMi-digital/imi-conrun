<?php

namespace IMI\Contao;

use IMI\Contao\Application\Console\Event;
use IMI\Util\OperatingSystem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    const WARNING_ROOT_USER = '<error>It\'s not recommended to run imi-conrun as root user</error>';

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            'imi-conrun.application.console.run.before' => 'checkRunningAsRootUser'
        );
    }

    /**
     * Display a warning if a running imi-conrun as root user
     *
     * @param ConsoleEvent $event
     * @return void
     */
    public function checkRunningAsRootUser(Event $event)
    {
        if ($this->_isSkipRootCheck()) {
            return;
        }
        $config = $event->getApplication()->getConfig();
        if (!$config['application']['check-root-user']) {
            return;
        }

        $output = $event->getOutput();
        if (OperatingSystem::isLinux() || OperatingSystem::isMacOs()) {
            if (function_exists('posix_getuid')) {
                if (posix_getuid() === 0) {
                    $output->writeln('');
                    $output->writeln(self::WARNING_ROOT_USER);
                    $output->writeln('');
                }
            }
        }
    }

    protected function _isSkipRootCheck()
    {
        $skipRootCheckOption = getopt('', array('skip-root-check'));

        return count($skipRootCheckOption) > 0;
    }
}
