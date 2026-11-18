<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Assembles the normaliser with every version mapper. Adding a mapper here is the second half of
 * a version bump (see AnalysisResultNormaliser); the order does not matter, each mapper declares
 * the version it reads.
 */
class AnalysisResultNormaliserFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AnalysisResultNormaliser
    {
        return new AnalysisResultNormaliser(new ReportMapper(), [
            // One per superseded version, e.g. new Version\Version1Mapper() once VERSION is 2.
        ]);
    }
}
