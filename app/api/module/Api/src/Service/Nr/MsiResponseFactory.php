<?php

namespace Dvsa\Olcs\Api\Service\Nr;

use Olcs\XmlTools\Xml\XmlNodeBuilder;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class MsiResponseFactory
 * @package Dvsa\Olcs\Api\Service\Nr
 */
class MsiResponseFactory implements FactoryInterface
{
    public const XML_NS_MSG = 'No config specified for xml ns';

    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return MsiResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MsiResponse
    {
        $config = $container->get('config');
        if (!isset($config['nr']['compliance_episode']['xmlNs'])) {
            throw new \RuntimeException(self::XML_NS_MSG);
        }
        $xmlBuilder = new XmlNodeBuilder('MS2ERRU_Infringement_Res', $config['nr']['compliance_episode']['xmlNs'], []);
        return new MsiResponse($xmlBuilder);
    }
}
