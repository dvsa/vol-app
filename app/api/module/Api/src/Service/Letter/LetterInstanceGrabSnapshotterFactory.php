<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class LetterInstanceGrabSnapshotterFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LetterInstanceGrabSnapshotter
    {
        return new LetterInstanceGrabSnapshotter(
            $container->get(VolGrabReplacementService::class),
            $container->get(VolGrabContextBuilder::class)
        );
    }
}
