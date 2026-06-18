<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FeeAmountSumFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return FeeAmountSum
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $formatterPluginManager = $container->get(FormatterPluginManager::class);
        $sumFormatter = $formatterPluginManager->get(Sum::class);
        $feeAmountFormatter = $formatterPluginManager->get(FeeAmount::class);
        return new FeeAmountSum($sumFormatter, $feeAmountFormatter);
    }
}
