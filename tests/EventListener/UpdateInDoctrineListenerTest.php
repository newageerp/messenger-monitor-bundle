<?php

declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\Tests\EventListener;

use KaroIO\MessengerMonitorBundle\EventListener\UpdateInDoctrineListener;
use KaroIO\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use KaroIO\MessengerMonitorBundle\Storage\StoredMessage;
use KaroIO\MessengerMonitorBundle\Storage\StoredMessageRepository;
use KaroIO\MessengerMonitorBundle\Tests\TestableMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

final class UpdateInDoctrineListenerTest extends TestCase
{
    public function testUpdateInDoctrineOnMessageReceived(): void
    {
        $listener = new UpdateInDoctrineListener(
            $storedMessageRepository = $this->createMock(StoredMessageRepository::class)
        );

        $envelope = new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()]);

        $storedMessageRepository->expects($this->once())
            ->method('findMessage')
            ->with($stamp->getId())
            ->willReturn($storedMessage = new StoredMessage($stamp->getId(), TestableMessage::class, new \DateTimeImmutable()));

        $storedMessageRepository->expects($this->once())
            ->method('updateMessage')
            ->with($storedMessage);

        $listener->onMessageReceived(new WorkerMessageReceivedEvent($envelope, 'receiver-name'));
        $this->assertNotNull($storedMessage->getReceivedAt());
    }

    public function testUpdateInDoctrineOnMessageHandled(): void
    {
        $listener = new UpdateInDoctrineListener(
            $storedMessageRepository = $this->createMock(StoredMessageRepository::class)
        );

        $envelope = new Envelope(new TestableMessage(), [$stamp = new MonitorIdStamp()]);

        $storedMessageRepository->expects($this->once())
            ->method('findMessage')
            ->with($stamp->getId())
            ->willReturn($storedMessage = new StoredMessage($stamp->getId(), TestableMessage::class, new \DateTimeImmutable(), new \DateTimeImmutable()));

        $storedMessageRepository->expects($this->once())
            ->method('updateMessage')
            ->with($storedMessage);

        $listener->onMessageHandled(new WorkerMessageHandledEvent($envelope, 'receiver-name'));
        $this->assertNotNull($storedMessage->getHandledAt());
    }
}
