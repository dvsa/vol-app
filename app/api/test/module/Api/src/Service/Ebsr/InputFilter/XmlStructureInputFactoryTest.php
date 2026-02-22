<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\InputFilter;

use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\XmlStructureInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\Operator;
use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\Registration;
use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\ServiceClassification;
use Dvsa\Olcs\Api\Service\Ebsr\XmlValidator\SupportingDocuments;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\XmlTools\Filter\ParseXml;
use Olcs\XmlTools\Validator\Xsd;
use Psr\Container\ContainerInterface;

class XmlStructureInputFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $maxSchemaErrors = 3;
        $schemaVersion = 2.5;
        $xmlMessageExclude = [];

        $config = [
            'ebsr' => [
                'validate' => [
                    'xml_structure' => true
                ],
                'max_schema_errors' => $maxSchemaErrors,
                'transxchange_schema_version' => $schemaVersion
            ],
            'xml_valid_message_exclude' => $xmlMessageExclude
        ];

        $mockXsdValidator = m::mock(\Laminas\Validator\AbstractValidator::class);
        $mockXsdValidator->shouldReceive('setXsd')->once()
            ->with('http://www.transxchange.org.uk/schema/' . $schemaVersion . '/TransXChange_registration.xsd');
        $mockXsdValidator->shouldReceive('setMaxErrors')->once()->with($maxSchemaErrors);
        $mockXsdValidator->shouldReceive('setXmlMessageExclude')->once()->with($xmlMessageExclude);

        $mockFilter = m::mock(\Laminas\Filter\AbstractFilter::class);
        $mockValidator = m::mock(\Laminas\Validator\AbstractValidator::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn($config);
        $mockSl->shouldReceive('get')->with('FilterManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('ValidatorManager')->andReturnSelf();

        $mockSl->shouldReceive('get')->with(ParseXml::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(Xsd::class)->andReturn($mockXsdValidator);
        $mockSl->shouldReceive('get')->with(Operator::class)->andReturn($mockValidator);
        $mockSl->shouldReceive('get')->with(Registration::class)->andReturn($mockValidator);
        $mockSl->shouldReceive('get')->with(ServiceClassification::class)->andReturn($mockValidator);
        $mockSl->shouldReceive('get')->with(SupportingDocuments::class)->andReturn($mockValidator);

        $sut = new XmlStructureInputFactory();
        $service = $sut->__invoke($mockSl, Input::class);

        $this->assertInstanceOf(\Laminas\InputFilter\Input::class, $service);
        $this->assertCount(1, $service->getFilterChain());
        $this->assertCount(5, $service->getValidatorChain());
    }

    /**
     * Tests create service with validation disabled
     */
    public function testInvokeDisabledValidators(): void
    {
        $config = [
            'ebsr' => [
                'validate' => [
                    'xml_structure' => false
                ]
            ]
        ];

        $mockFilter = m::mock(\Laminas\Filter\AbstractFilter::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn($config);
        $mockSl->shouldReceive('get')->with('FilterManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with(ParseXml::class)->andReturn($mockFilter);

        $sut = new XmlStructureInputFactory();
        $service = $sut->__invoke($mockSl, Input::class);

        $this->assertInstanceOf(\Laminas\InputFilter\Input::class, $service);
        $this->assertCount(1, $service->getFilterChain());
        $this->assertCount(0, $service->getValidatorChain());
    }

    /**
     * test correct exception thrown when the max errors config is missing
     */
    public function testInvokeMissingMaxErrorsConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No config specified for max_schema_errors');

        $config = [
            'ebsr' => [
                'validate' => [
                    'xml_structure' => true
                ]
            ]
        ];

        $mockFilter = m::mock(\Laminas\Filter\AbstractFilter::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->once()->andReturn($config);
        $mockSl->shouldReceive('get')->with('FilterManager')->once()->andReturnSelf();
        $mockSl->shouldReceive('get')->with(ParseXml::class)->once()->andReturn($mockFilter);

        $sut = new XmlStructureInputFactory();
        $sut->__invoke($mockSl, Input::class);
    }

    /**
     * test correct exception thrown when the max errors config is missing
     */
    public function testInvokeMissingSchemaVersionConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No config specified for transxchange schema version');

        $config = [
            'ebsr' => [
                'validate' => [
                    'xml_structure' => true
                ],
                'max_schema_errors' => 3
            ]
        ];

        $mockFilter = m::mock(\Laminas\Filter\AbstractFilter::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->once()->andReturn($config);
        $mockSl->shouldReceive('get')->with('FilterManager')->once()->andReturnSelf();
        $mockSl->shouldReceive('get')->with(ParseXml::class)->once()->andReturn($mockFilter);

        $sut = new XmlStructureInputFactory();
        $sut->__invoke($mockSl, Input::class);
    }

    /**
     * test correct exception thrown when the max errors config is missing
     */
    public function testInvokeMissingXmlMessageExclude(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No config specified for xml messages to exclude');

        $config = [
            'ebsr' => [
                'validate' => [
                    'xml_structure' => true
                ],
                'max_schema_errors' => 3,
                'transxchange_schema_version' => 2.5
            ]
        ];

        $mockFilter = m::mock(\Laminas\Filter\AbstractFilter::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->once()->andReturn($config);
        $mockSl->shouldReceive('get')->with('FilterManager')->once()->andReturnSelf();
        $mockSl->shouldReceive('get')->with(ParseXml::class)->once()->andReturn($mockFilter);

        $sut = new XmlStructureInputFactory();
        $sut->__invoke($mockSl, Input::class);
    }
}
