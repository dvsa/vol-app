<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererPluginManager;
use Dvsa\Olcs\Api\Service\Letter\VolGrabReplacementService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for LetterPreviewService
 */
class LetterPreviewServiceFactory implements FactoryInterface
{
    /**
     * Create LetterPreviewService
     *
     * @param ContainerInterface $container Service container
     * @param string $requestedName Requested service name
     * @param array|null $options Creation options
     * @return LetterPreviewService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LetterPreviewService
    {
        $repoManager = $container->get('RepositoryServiceManager');

        return new LetterPreviewService(
            $container->get(SectionRendererPluginManager::class),
            $container->get('ContentStore'),
            $repoManager->get('DocTemplate'),
            $container->get(VolGrabReplacementService::class)
        );
    }
}
