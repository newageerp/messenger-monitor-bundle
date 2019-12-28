<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\EventListener;

use KaroIO\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use KaroIO\MessengerMonitorBundle\Storage\StoredMessage;
use KaroIO\MessengerMonitorBundle\Storage\StoredMessageRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/**
 * todo: see how retries fit into this.
 *
 * @internal
 */
final class UpdateInDoctrineListener implements EventSubscriberInterface
{
    private $storedMessageRepository;

    public function __construct(StoredMessageRepository $storedMessageRepository)
    {
        $this->storedMessageRepository = $storedMessageRepository;
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        $storedMessage = $this->getStoredMessage($event->getEnvelope());

        $storedMessage->setReceivedAt(\DateTimeImmutable::createFromFormat('U', (string) time()));
        $this->storedMessageRepository->updateMessage($storedMessage);
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        $storedMessage = $this->getStoredMessage($event->getEnvelope());

        $storedMessage->setHandledAt(\DateTimeImmutable::createFromFormat('U', (string) time()));
        $this->storedMessageRepository->updateMessage($storedMessage);
    }

    private function getStoredMessage(Envelope $envelope): StoredMessage
    {
        /** @var MonitorIdStamp $monitorIdStamp */
        $monitorIdStamp = $envelope->last(MonitorIdStamp::class);

        if (null === $monitorIdStamp) {
            throw new \RuntimeException('Envelope should have a MonitorIdStamp!');
        }

        $storedMessage = $this->storedMessageRepository->findMessage($monitorIdStamp->getId());

        if (null === $storedMessage) {
            throw new \RuntimeException(sprintf('Message with id "%s" not found', $monitorIdStamp->getId()));
        }

        return $storedMessage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => 'onMessageReceived',
            WorkerMessageHandledEvent::class => 'onMessageHandled',
        ];
    }
}
