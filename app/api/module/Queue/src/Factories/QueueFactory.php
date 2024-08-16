<?php

namespace Dvsa\Olcs\Queue\Factories;

use Aws\Sqs\SqsClient;
use Dvsa\Olcs\Queue\Service\Queue;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class QueueFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Queue
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Queue
    {
        /**
         * @var $sqsClient SqsClient
         */
        $sqsClient = $container->get('SqsClient');
        $queueService = new Queue($sqsClient);
        return $queueService;
    }
}
