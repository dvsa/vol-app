<?php

namespace Dvsa\Olcs\Queue\Factories;

use Dvsa\Olcs\Queue\Service\Message\MessageBuilder;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class MessageBuilderFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return MessageBuilder
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MessageBuilder
    {
        return new MessageBuilder();
    }
}
