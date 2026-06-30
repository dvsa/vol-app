<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TmApplicationManagerTypeFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return TmApplicationManagerType
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $application = $container->get('Application');
        $urlHelper = $container->get('Helper\Url');
        $translator = $container->get('translator');
        return new TmApplicationManagerType($application, $urlHelper, $translator);
    }
}
