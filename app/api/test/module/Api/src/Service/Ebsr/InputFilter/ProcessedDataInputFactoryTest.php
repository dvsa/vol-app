<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\InputFilter;

use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ProcessedDataInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\BusRegNotFound;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\LocalAuthorityMissing;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\NewAppAlreadyExists;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\RegisteredBusRoute;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\VariationNumber;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Container\ContainerInterface;

class ProcessedDataInputFactoryTest extends TestCase
{
    /**
     * Tests create service
     */
    public function testInvoke(): void
    {
        $mockValidator = m::mock(\Laminas\Validator\AbstractValidator::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn([]);
        $mockSl->shouldReceive('get')->with('ValidatorManager')->andReturnSelf();

        $mockSl->shouldReceive('get')->with(BusRegNotFound::class)->once()->andReturn($mockValidator);
        $mockSl->shouldReceive('get')->with(VariationNumber::class)->once()->andReturn($mockValidator);
        $mockSl->shouldReceive('get')->with(NewAppAlreadyExists::class)->once()->andReturn($mockValidator);
        $mockSl->shouldReceive('get')->with(RegisteredBusRoute::class)->once()->andReturn($mockValidator);
        $mockSl->shouldReceive('get')->with(LocalAuthorityMissing::class)->once()->andReturn($mockValidator);

        $sut = new ProcessedDataInputFactory();
        $service = $sut->__invoke($mockSl, Input::class);

        $this->assertInstanceOf(Input::class, $service);
        $this->assertCount(5, $service->getValidatorChain());
    }

    /**
     * Tests create service with disabled validators
     */
    public function testInvokeDisabledValidators(): void
    {
        $config = [
            'ebsr' => [
                'validate' => [
                    'processed_data' => false
                ]
            ]
        ];

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn($config);

        $sut = new ProcessedDataInputFactory();
        $service = $sut->__invoke($mockSl, Input::class);

        $this->assertInstanceOf(Input::class, $service);
        $this->assertCount(0, $service->getValidatorChain());
    }
}
