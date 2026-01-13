<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Nr\InputFilter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\XmlTools\Filter\ParseXmlString;
use Olcs\XmlTools\Validator\Xsd;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Psr\Container\ContainerInterface;

class CgrInputFactory implements FactoryInterface
{
    public const MAX_SCHEMA_MSG = 'No config specified for max_schema_errors';
    public const XML_VALID_EXCLUDE_MSG = 'No config specified for xml messages to exclude';
    public const SCHEMA_PATH = '/../../../data/nr/xsd/CheckGoodRepute_Response.xsd';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Input
    {
        $config = $container->get('config');

        if (!isset($config['nr']['max_schema_errors'])) {
            throw new \RuntimeException(self::MAX_SCHEMA_MSG);
        }
        if (!isset($config['xml_valid_message_exclude'])) {
            throw new \RuntimeException(self::XML_VALID_EXCLUDE_MSG);
        }

        $service = new Input('cgr_input');
        $filterChain = $service->getFilterChain();
        $filterChain->attach($container->get('FilterManager')->get(ParseXmlString::class));
        $validatorChain = $service->getValidatorChain();
        /** @var Xsd $xsdValidator */
        $xsdValidator = $container->get('ValidatorManager')->get(Xsd::class);
        $xsdValidator->setXsd(dirname(__DIR__) . self::SCHEMA_PATH);
        $xsdValidator->setMaxErrors($config['nr']['max_schema_errors']);
        $xsdValidator->setXmlMessageExclude($config['xml_valid_message_exclude']);
        $validatorChain->attach($xsdValidator);
        return $service;
    }
}
