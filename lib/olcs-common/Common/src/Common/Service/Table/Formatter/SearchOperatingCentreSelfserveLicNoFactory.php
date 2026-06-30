<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SearchOperatingCentreSelfserveLicNoFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SearchOperatingCentreSelfserveLicNo
    {
        $translator = $container->get('translator');
        return new SearchOperatingCentreSelfserveLicNo($translator);
    }
}
