<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Nr;

use Olcs\XmlTools\Xml\XmlNodeBuilder;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CheckGoodReputeFactory implements FactoryInterface
{
    public const XML_NS_MSG = 'No config specified for xml ns';
    public const XML_VERSION_MSG = 'No config specified for erru version';
    public const ROOT_ELEMENT = 'CheckGoodRepute_Request';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CheckGoodRepute
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

        $xmlBuilder = new XmlNodeBuilder(self::ROOT_ELEMENT, $ns . $erruVersion, []);
        return new CheckGoodRepute($xmlBuilder, $erruVersion);
    }
}
