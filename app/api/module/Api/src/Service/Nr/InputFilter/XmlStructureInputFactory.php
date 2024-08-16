<?php

namespace Dvsa\Olcs\Api\Service\Nr\InputFilter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\XmlTools\Validator\Xsd;
use Olcs\XmlTools\Filter\ParseXmlString;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Psr\Container\ContainerInterface;

/**
 * Class XmlStructureInputFactory
 * @package Dvsa\Olcs\Api\Service\Nr\InputFilter
 */
class XmlStructureInputFactory implements FactoryInterface
{
    public const MAX_SCHEMA_MSG = 'No config specified for max_schema_errors';
    public const XML_VALID_EXCLUDE_MSG = 'No config specified for xml messages to exclude';
    public const XML_NS_MSG = 'No config specified for xml ns';

    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Input
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Input
    {
        $config = $container->get('config');
        if (!isset($config['nr']['compliance_episode']['xmlNs'])) {
            throw new \RuntimeException(self::XML_NS_MSG);
        }
        if (!isset($config['nr']['max_schema_errors'])) {
            throw new \RuntimeException(self::MAX_SCHEMA_MSG);
        }
        if (!isset($config['xml_valid_message_exclude'])) {
            throw new \RuntimeException(self::XML_VALID_EXCLUDE_MSG);
        }
        $service = new Input('xml_structure');
        $filterChain = $service->getFilterChain();
        $filterChain->attach($container->get('FilterManager')->get(ParseXmlString::class));
        $validatorChain = $service->getValidatorChain();
        /** @var Xsd $xsdValidator */
        $xsdValidator = $container->get('ValidatorManager')->get(Xsd::class);
        $xsdValidator->setXsd($config['nr']['compliance_episode']['xmlNs']);
        $xsdValidator->setMaxErrors($config['nr']['max_schema_errors']);
        $xsdValidator->setXmlMessageExclude($config['xml_valid_message_exclude']);
        $validatorChain->attach($xsdValidator);
        return $service;
    }
}
