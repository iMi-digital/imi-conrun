<?php

namespace IMI\Contao\Command\ComposerWrapper;

use Composer\Factory;
use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array('console.command' => 'registerComposer');
    }

    /**
     * @param ConsoleEvent $event
     */
    public function registerComposer(ConsoleEvent $event)
    {
        /*
         * Inject composer object in composer commands
         */
        $command = $event->getCommand();
        if (strstr(get_class($command), 'Composer\\Command\\')) {
            $io = new ConsoleIO($event->getInput(), $event->getOutput(), $command->getHelperSet());
            $contaoRootFolder = $command->getApplication()->getContaoRootFolder();
            $configFile = $contaoRootFolder . '/composer.json';
            $composer = Factory::create($io, $configFile);
            \chdir($contaoRootFolder);
            $command->setComposer($composer);
            $command->setIO($io);
        }
    }
}
