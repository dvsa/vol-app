<?php

namespace Olcs\XmlTools\Validator;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class XsdFactory
 * @package Olcs\XmlTools\Validator
 */
class XsdFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Xsd
    {
        $config = $container->get('Config');

        $xsd = new Xsd();

        if (isset($config['xsd_mappings'])) {
            $xsd->setMappings($config['xsd_mappings']);
        }

        return $xsd;
    }
}
