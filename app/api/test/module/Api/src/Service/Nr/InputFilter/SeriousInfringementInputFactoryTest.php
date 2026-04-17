<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nr\InputFilter;

use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Dvsa\Olcs\Api\Service\Nr\Filter\Format\IsExecuted;
use Dvsa\Olcs\Api\Service\Nr\Filter\Format\SiDates as SiDatesFilter;
use Dvsa\Olcs\Api\Service\Nr\InputFilter\SeriousInfringementInputFactory;
use Dvsa\Olcs\Api\Service\Nr\Validator\SiPenaltyImposedDate;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Container\ContainerInterface;

/**
 * Class SeriousInfringementInputFactoryTest
 * @package Dvsa\OlcsTest\Api\Service\Nr\InputFilter
 */
class SeriousInfringementInputFactoryTest extends TestCase
{
    public function testCreateService(): void
    {
        $mockFilter = m::mock(\Laminas\Filter\AbstractFilter::class);
        $mockValidator = m::mock(\Laminas\Validator\AbstractValidator::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('FilterManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('ValidatorManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with(IsExecuted::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(SiDatesFilter::class)->andReturn($mockFilter);
        $mockSl->shouldReceive('get')->with(SiPenaltyImposedDate::class)->andReturn($mockValidator);

        $sut = new SeriousInfringementInputFactory();

        $service = $sut->__invoke($mockSl, Input::class);

        $this->assertInstanceOf(Input::class, $service);
        $this->assertCount(2, $service->getFilterChain());
        $this->assertCount(1, $service->getValidatorChain());
    }
}
