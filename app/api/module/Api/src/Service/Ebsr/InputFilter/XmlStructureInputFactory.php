<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\InputFilter;

use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\Operator;
use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\Registration;
use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\ServiceClassification;
use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\SupportingDocuments;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\XmlTools\Validator\Xsd;
use Olcs\XmlTools\Filter\ParseXml;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Psr\Container\ContainerInterface;

/**
 * Class XmlStructureInputFactory
 * @package Dvsa\Olcs\Api\Service\Ebsr\InputFilter
 */
class XmlStructureInputFactory implements FactoryInterface
{
    public const MAX_SCHEMA_MSG = 'No config specified for max_schema_errors';
    public const SCHEMA_VERSION_MSG = 'No config specified for transxchange schema version';
    public const XML_VALID_EXCLUDE_MSG = 'No config specified for xml messages to exclude';
    public const XSD_PATH = 'http://www.transxchange.org.uk/schema/%s/TransXChange_registration.xsd';

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
        $inputName = 'xml_structure';
        $service = new Input($inputName);
        $config = $container->get('config');
        $filterChain = $service->getFilterChain();
        $filterChain->attach($container->get('FilterManager')->get(ParseXml::class));
        $validatorchain = $service->getValidatorChain();
        //allows validators to be switched off (debug only, not to be used for production)
        if (!isset($config['ebsr']['validate'][$inputName]) || $config['ebsr']['validate'][$inputName] === true) {
            if (!isset($config['ebsr']['max_schema_errors'])) {
                throw new \RuntimeException(self::MAX_SCHEMA_MSG);
            }

            if (!isset($config['ebsr']['transxchange_schema_version'])) {
                throw new \RuntimeException(self::SCHEMA_VERSION_MSG);
            }

            if (!isset($config['xml_valid_message_exclude'])) {
                throw new \RuntimeException(self::XML_VALID_EXCLUDE_MSG);
            }

            $validatorManager = $container->get('ValidatorManager');

            /** @var Xsd $xsdValidator */
            $xsdValidator = $validatorManager->get(Xsd::class);
            $xsdValidator->setXsd(sprintf(self::XSD_PATH, $config['ebsr']['transxchange_schema_version']));
            $xsdValidator->setMaxErrors($config['ebsr']['max_schema_errors']);
            $xsdValidator->setXmlMessageExclude($config['xml_valid_message_exclude']);

            $validatorchain->attach($xsdValidator);
            $validatorchain->attach($validatorManager->get(ServiceClassification::class));
            $validatorchain->attach($validatorManager->get(Operator::class));
            $validatorchain->attach($validatorManager->get(Registration::class));
            $validatorchain->attach($validatorManager->get(SupportingDocuments::class));
        }
        return $service;
    }
}
