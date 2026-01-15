<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Service\Nr\CheckGoodRepute;
use Dvsa\Olcs\Api\Service\Nr\InrClientInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class CheckReputeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $inrClient = $container->build(InrClientInterface::class, ['path' => '/outbound/message/requests/cgr']);
        $checkGoodReputeService = $container->get(CheckGoodRepute::class);
        return (new CheckRepute($inrClient, $checkGoodReputeService))->__invoke($container, $requestedName, $options);
    }
}
