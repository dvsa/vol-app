<?php

namespace Dvsa\Olcs\Api\Service\Nr;

use Olcs\XmlTools\Xml\XmlNodeBuilder;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class MsiResponseFactory implements FactoryInterface
{
    public const XML_NS_MSG = 'No config specified for xml ns';
    public const XML_VERSION_MSG = 'No config specified for erru version';

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

        if (!isset($config['nr']['compliance_episode']['erruVersion'])) {
            throw new \RuntimeException(self::XML_VERSION_MSG);
        }

        $ns = $config['nr']['compliance_episode']['xmlNs'];
        $erruVersion = $config['nr']['compliance_episode']['erruVersion'];

        $xmlBuilder = new XmlNodeBuilder('NotifyCheckResult_Response', $ns . $erruVersion, []);
        return new MsiResponse($xmlBuilder, $erruVersion);
    }
}
