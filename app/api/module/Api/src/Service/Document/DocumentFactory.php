<?php

namespace Dvsa\Olcs\Api\Service\Document;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DocumentFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Document(
            $container->get('DateService'),
            $container->get('ContentStore'),
            $container->get('translator')
        );
    }
}
