<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\InputFilter;

use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ShortNoticeInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice\MissingReason;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice\MissingSection;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Container\ContainerInterface;

class ShortNoticeInputFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $mockValidator = m::mock(\Laminas\Validator\AbstractValidator::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn([]);
        $mockSl->shouldReceive('get')->with('ValidatorManager')->andReturnSelf();

        $mockSl->shouldReceive('get')->with(MissingSection::class)->once()->andReturn($mockValidator);
        $mockSl->shouldReceive('get')->with(MissingReason::class)->once()->andReturn($mockValidator);

        $sut = new ShortNoticeInputFactory();
        $service = $sut->__invoke($mockSl, Input::class);

        $this->assertInstanceOf(Input::class, $service);
        $this->assertCount(2, $service->getValidatorChain());
    }

    /**
     * Tests create service with disabled validators
     */
    public function testInvokeDisabledValidators(): void
    {
        $config = [
            'ebsr' => [
                'validate' => [
                    'short_notice' => false
                ]
            ]
        ];

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn($config);

        $sut = new ShortNoticeInputFactory();
        $service = $sut->__invoke($mockSl, Input::class);

        $this->assertInstanceOf(Input::class, $service);
        $this->assertCount(0, $service->getValidatorChain());
    }
}
