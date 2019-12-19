<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\EventListener;

use KaroIO\MessengerMonitorBundle\Stamp\MonitorIdStamp;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

final class AddStampOnMessageSentListener implements EventSubscriberInterface
{
    public function onMessageSent(SendMessageToTransportsEvent $event): void
    {
        $event->setEnvelope(
            $event->getEnvelope()->with(new MonitorIdStamp())
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SendMessageToTransportsEvent::class => 'onMessageSent',
        ];
    }
}
