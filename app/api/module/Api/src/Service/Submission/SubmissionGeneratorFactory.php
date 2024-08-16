<?php

namespace Dvsa\Olcs\Api\Service\Submission;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorPluginManager as SectionGeneratorPluginManager;
use Psr\Container\ContainerInterface;

/**
 * Class SubmissionGeneratorFactory
 * @package Dvsa\Olcs\Api\Service\Submission
 */
class SubmissionGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SubmissionGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SubmissionGenerator
    {
        return new SubmissionGenerator(
            $container->get('config')['submissions'],
            $container->get(SectionGeneratorPluginManager::class)
        );
    }
}
