<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\InputFilter;

use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\ExistingRegNo;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\MiscSnJustification;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Subsidy;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Via;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectIsTxcApp;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectNaptanCodes;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectReceivedDate;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\NoticePeriod;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\BusRegistrationInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ApplicationType;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\EffectiveDate;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\EndDate;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\Licence;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ServiceNo;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\XmlTools\Filter\MapXmlFile;
use Olcs\XmlTools\Xml\Specification\SpecificationInterface;
use Psr\Container\ContainerInterface;

class BusRegistrationInputFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $mockMappings = m::mock(SpecificationInterface::class);

        $mockFilter = m::mock(\Laminas\Filter\AbstractFilter::class);
        $mockValidator = m::mock(\Laminas\Validator\AbstractValidator::class);
        $mockBreakValidator = m::mock(\Laminas\Validator\AbstractValidator::class);

        $mockMapFilter = m::mock(MapXmlFile::class);
        $mockMapFilter->shouldReceive('setMapping')->with($mockMappings);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn([]);
        $mockSl->shouldReceive('get')->with('FilterManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('ValidatorManager')->andReturnSelf();

        $mockSl->shouldReceive('get')->with('TransExchangeXmlMapping')->andReturn($mockMappings);

        $mockSl->shouldReceive('get')->with(MapXmlFile::class)->andReturn($mockMapFilter);
        $mockSl->shouldReceive('get')->with(InjectIsTxcApp::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(InjectReceivedDate::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(InjectNaptanCodes::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(NoticePeriod::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(Subsidy::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(Via::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(ExistingRegNo::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(MiscSnJustification::class)->andReturn($mockFilter);

        $mockSl->shouldReceive('get')->with(EffectiveDate::class)->andReturn($mockValidator);
        $mockSl->shouldReceive('get')->with(ApplicationType::class)->andReturn($mockValidator);
        $mockSl->shouldReceive('get')->with(Licence::class)->andReturn($mockValidator);
        $mockSl->shouldReceive('get')->with(ServiceNo::class)->andReturn($mockValidator);
        $mockSl->shouldReceive('get')->with(EndDate::class)->andReturn($mockValidator);

        $sut = new BusRegistrationInputFactory();
        /** @var Input $service */
        $service = $sut->__invoke($mockSl, Input::class);

        $this->assertInstanceOf(Input::class, $service);
        $this->assertCount(9, $service->getFilterChain());
        $this->assertCount(5, $service->getValidatorChain());

        foreach ($service->getValidatorChain()->getValidators() as $validator) {
            if ($validator['instance'] === $mockBreakValidator) {
                $this->assertTrue($validator['breakChainOnFailure'], 'Break chain on failure set incorrectly');
            } else {
                $this->assertFalse($validator['breakChainOnFailure'], 'Break chain on failure set incorrectly');
            }
        }
    }

    /**
     * Tests create service with disabled validators
     */
    public function testInvokeDisabledValidators(): void
    {
        $config = [
            'ebsr' => [
                'validate' => [
                    'bus_registration' => false
                ]
            ]
        ];
        $mockMappings = m::mock(SpecificationInterface::class);

        $mockFilter = m::mock(\Laminas\Filter\AbstractFilter::class);

        $mockMapFilter = m::mock(MapXmlFile::class);
        $mockMapFilter->shouldReceive('setMapping')->with($mockMappings);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn($config);
        $mockSl->shouldReceive('get')->with('FilterManager')->andReturnSelf();

        $mockSl->shouldReceive('get')->with('TransExchangeXmlMapping')->andReturn($mockMappings);

        $mockSl->shouldReceive('get')->with(MapXmlFile::class)->andReturn($mockMapFilter);
        $mockSl->shouldReceive('get')->with(InjectIsTxcApp::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(InjectReceivedDate::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(InjectNaptanCodes::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(NoticePeriod::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(Subsidy::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(Via::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(ExistingRegNo::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(MiscSnJustification::class)->andReturn($mockFilter);

        $sut = new BusRegistrationInputFactory();
        /** @var Input $service */
        $service = $sut->__invoke($mockSl, Input::class);

        $this->assertInstanceOf(Input::class, $service);
        $this->assertCount(9, $service->getFilterChain());
        $this->assertCount(0, $service->getValidatorChain());
    }
}
