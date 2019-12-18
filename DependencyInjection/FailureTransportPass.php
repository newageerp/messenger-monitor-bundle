<?php declare(strict_types=1);

namespace KaroIO\MessengerMonitorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FailureTransportPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('karo-io.messenger_monitor.failure_transport_locator')) {
            if ($container->hasDefinition('console.command.messenger_failed_messages_show')) {
                // steal configurations already done by the MessengerPass so we dont have to duplicate the work
                $receiverLocatorDefinition = $container->getDefinition('karo-io.messenger_monitor.failure_transport_locator');

                $consumeCommandDefinition = $container->getDefinition('console.command.messenger_failed_messages_show');
                $receiverLocatorDefinition->replaceArgument(1, $consumeCommandDefinition->getArgument(0));
            }
        }
    }

}