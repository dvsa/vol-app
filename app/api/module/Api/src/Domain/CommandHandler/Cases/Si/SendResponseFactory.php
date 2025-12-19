<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si;

use Dvsa\Olcs\Api\Service\Nr\InrClientInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class SendResponseFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $inrClient = $container->build(InrClientInterface::class, ['path' => '/outbound/message/response/ncr']);
        return (new SendResponse($inrClient))->__invoke($container, $requestedName, $options);
    }
}
