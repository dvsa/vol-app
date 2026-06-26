<?php

declare(strict_types=1);

namespace Common\Service\Table\Formatter;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ExternalConversationStatusFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return ExternalConversationStatus
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ExternalConversationStatus();
    }
}
