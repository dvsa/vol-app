<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TransactionFeeStatusFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return TransactionFeeStatus
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $router = $container->get('Router');
        $request = $container->get('Request');
        $urlHelper = $container->get('Helper\Url');

        return new TransactionFeeStatus($router, $request, $urlHelper);
    }
}
