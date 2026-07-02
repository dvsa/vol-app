<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class IrhpPermitTypeWithValidityDateFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return IrhpPermitTypeWithValidityDate
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $formatterPluginManager = $container->get(FormatterPluginManager::class);
        $dateFormatter = $formatterPluginManager->get(Date::class);
        $translator = $container->get('translator');
        return new IrhpPermitTypeWithValidityDate($dateFormatter, $translator);
    }
}
