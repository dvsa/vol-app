<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\Mapping;

use Olcs\XmlTools\Xml\Specification\MultiNodeValue;
use Olcs\XmlTools\Xml\Specification\NodeValue;
use Olcs\XmlTools\Xml\Specification\Recursion;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class TransExchangePublisherXmlFactory
 * @package Olcs\Ebsr\Data\Mapping
 */
class TransExchangePublisherXmlFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Recursion
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Recursion
    {
        $publisherResponse = [
            'BadRequest' => new NodeValue('error'),
            'Failed' => new NodeValue('error'),
            'Completed' => new Recursion('OutputFiles', new Recursion('OutputFile', new MultiNodeValue('files')))
        ];
        return new Recursion('PublisherResponse', new Recursion($publisherResponse));
    }
}
