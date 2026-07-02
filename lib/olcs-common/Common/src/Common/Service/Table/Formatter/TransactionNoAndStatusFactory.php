<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TransactionNoAndStatusFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return TransactionNoAndStatus
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $formatterPluginManager = $container->get(FormatterPluginManager::class);
        $transactionUrlFormatter = $formatterPluginManager->get(TransactionUrl::class);
        $trasactionStatusFormatter = $formatterPluginManager->get(TransactionStatus::class);
        return new TransactionNoAndStatus($transactionUrlFormatter, $trasactionStatusFormatter);
    }
}
