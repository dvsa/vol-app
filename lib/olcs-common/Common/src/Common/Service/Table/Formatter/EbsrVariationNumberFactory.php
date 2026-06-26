<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class EbsrVariationNumberFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return EbsrVariationNumber
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $viewHelperManager = $container->get('ViewHelperManager');
        $translator = $container->get('translator');
        return new EbsrVariationNumber($viewHelperManager, $translator);
    }
}
