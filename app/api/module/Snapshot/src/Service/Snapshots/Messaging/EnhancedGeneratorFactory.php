<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Messaging;

use Dvsa\Olcs\Api\Domain\Repository\Message as MessageRepository;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EnhancedGeneratorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new EnhancedGenerator(
            $container->get(AbstractGeneratorServices::class),
            $container->get('RepositoryServiceManager')->get(MessageRepository::class),
        );
    }
}
