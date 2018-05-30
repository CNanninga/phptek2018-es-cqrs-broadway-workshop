<?php

namespace SuperAwesome\Symfony\BlogBundle\Command;

use Broadway\Domain\DomainEventStream;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\EventStore\CallableEventVisitor;

use Broadway\EventStore\Management\Criteria;
use SuperAwesome\Common\Infrastructure\EventBus\EventBus;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RebuildCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('superawesome:blog:rebuild');
        $this->setDescription('Rebuild all the things');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventStore = $this->getContainer()->get('broadway.event_store');


        $eventDispatcher = $this->getContainer('broadway.event_handling.event_bus');

        $eventStore->visitEvents(
            Criteria::create(),
            new CallableEventVisitor(function($event) use ($eventDispatcher) {
                $eventDispatcher->publish(new DomainEventStream([$event]));
            })
        );
    }
}