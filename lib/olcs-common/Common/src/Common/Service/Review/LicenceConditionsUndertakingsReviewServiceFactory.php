<?php

namespace Common\Service\Review;

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\FormatterPluginManager;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LicenceConditionsUndertakingsReviewServiceFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceConditionsUndertakingsReviewService
    {
        return new LicenceConditionsUndertakingsReviewService(
            $container->get(AbstractReviewServiceServices::class),
            $container->get('Review\ConditionsUndertakings'),
            $container->get(FormatterPluginManager::class)->get(Address::class)
        );
    }
}
