<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FeeTransactionDateFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return FeeTransactionDate
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $dateFormatter = $container->get(Date::class);
        $stackValueFormatter = $container->get(StackValue::class);
        return new FeeTransactionDate($dateFormatter, $stackValueFormatter);
    }
}
