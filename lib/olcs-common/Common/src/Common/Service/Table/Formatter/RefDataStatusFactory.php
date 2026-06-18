<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RefDataStatusFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return RefDataStatus
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $refDataFormatter = $container->get(FormatterPluginManager::class)->get(RefData::class);
        $viewHelperManager = $container->get('ViewHelperManager');
        return new RefDataStatus($viewHelperManager, $refDataFormatter);
    }
}
