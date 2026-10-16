<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Aws\S3\S3Client;
use Aws\Sfn\SfnClient;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * StoreDocumentAnalysisResult takes constructor arguments, so it cannot be mapped directly in
 * command-map.config.php - handlers mapped by class name are instantiated with no arguments.
 */
class StoreDocumentAnalysisResultFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): StoreDocumentAnalysisResult
    {
        $instance = new StoreDocumentAnalysisResult(
            $container->get(SfnClient::class),
            $container->get(S3Client::class),
        );

        return $instance->__invoke($container, $requestedName, $options);
    }
}
