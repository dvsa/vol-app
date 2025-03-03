<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Nr\Mapping;

use Olcs\XmlTools\Filter\MapXmlFile;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CgrResponseXmlFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ComplianceEpisodeXml
    {
        $config = $container->get('config');
        if (!isset($config['nr']['compliance_episode']['xmlNs'])) {
            throw new \RuntimeException('Missing xmlNs for INR config');
        }

        if (!isset($config['nr']['compliance_episode']['erruVersion'])) {
            throw new \RuntimeException('Missing erruVersion for INR config');
        }

        $ns = $config['nr']['compliance_episode']['xmlNs'];
        $erruVersion = $config['nr']['compliance_episode']['erruVersion'];

        $mapXmlFile = $container->get('FilterManager')->get(MapXmlFile::class);
        return new ComplianceEpisodeXml($mapXmlFile, $ns . $erruVersion);
    }
}
