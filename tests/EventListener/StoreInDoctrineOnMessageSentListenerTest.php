<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\EventListener;

use KaroIO\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use KaroIO\MessengerMonitorBundle\Storage\DoctrineConnection;
use KaroIO\MessengerMonitorBundle\Storage\StoredMessage;
use KaroIO\MessengerMonitorBundle\Test\Message;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

final class StoreInDoctrineOnMessageSentListenerTest extends TestCase
{
    public function testStoreInDoctrineOnMessageSent(): void
    {
        $listener = new StoreInDoctrineOnMessageSentListener(
            $doctrineConnection = $this->createMock(DoctrineConnection::class)
        );

        $envelope = new Envelope(new Message(), [new MonitorIdStamp()]);

        $doctrineConnection->expects($this->once())
            ->method('saveMessage')
            ->with(StoredMessage::fromEnvelope($envelope));

        $listener->onMessageSent(new SendMessageToTransportsEvent($envelope));

    }
}
