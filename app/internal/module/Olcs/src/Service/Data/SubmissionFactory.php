<?php

namespace Olcs\Service\Data;

use Common\Service\Data\RefData;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SubmissionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return Submission
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Submission
    {
        return new Submission(
            $container->get(RefData::class)
        );
    }
}
