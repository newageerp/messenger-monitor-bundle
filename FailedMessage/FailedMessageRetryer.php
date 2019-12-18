<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\FailedMessage;

use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverDoesNotExistException;
use KaroIO\MessengerMonitorBundle\Exception\FailureReceiverNotListableException;
use KaroIO\MessengerMonitorBundle\FailureReceiver\FailureReceiverName;
use KaroIO\MessengerMonitorBundle\Locator\ReceiverLocator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\SingleMessageReceiver;
use Symfony\Component\Messenger\Worker;

/**
 * all this code was stolen from \Symfony\Component\Messenger\Command\FailedMessagesRetryCommand
 *
 * @internal
 */
class FailedMessageRetryer
{
    private $receiverLocator;
    private $failureReceiverName;
    private $eventDispatcher;
    private $messageBus;
    private $logger;

    public function __construct(ReceiverLocator $receiverLocator, FailureReceiverName $failureReceiverName, MessageBusInterface $messageBus, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        $this->receiverLocator = $receiverLocator;
        $this->failureReceiverName = $failureReceiverName;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    public function retryFailedMessage($id): void
    {
        $this->eventDispatcher->addSubscriber(new StopWorkerOnMessageLimitListener(1));

        if (null === $this->failureReceiverName->toString()) {
            throw new FailureReceiverDoesNotExistException();
        }

        $failureReceiver = $this->receiverLocator->getReceiver($this->failureReceiverName->toString());

        if (!$failureReceiver instanceof ListableReceiverInterface) {
            throw new FailureReceiverNotListableException();
        }

        $envelope = $failureReceiver->find($id);
        if (null === $envelope) {
            throw new \RuntimeException(sprintf('The message "%s" was not found.', $id));
        }

        $singleReceiver = new SingleMessageReceiver($failureReceiver, $envelope);
        $worker = new Worker(
            [$this->failureReceiverName->toString() => $singleReceiver],
            $this->messageBus,
            $this->eventDispatcher,
            $this->logger
        );
        $worker->run();
    }
}
