<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\PrintScheduler;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PrintJobFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PrintJob
    {
        $instance = new PrintJob(
            $container->get('config'),
            $container->get('FileUploader'),
            $container->get('ConvertToPdf')
        );

        return $instance->__invoke($container, $requestedName, $options);
    }
}
