<?php

namespace Common\Service\Review;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AbstractReviewServiceServicesFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AbstractReviewServiceServices
    {
        return new AbstractReviewServiceServices(
            $container->get('Helper\Translation')
        );
    }
}
