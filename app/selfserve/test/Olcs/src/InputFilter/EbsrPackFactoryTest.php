<?php

declare(strict_types=1);

namespace OlcsTest\InputFilter;

use Psr\Container\ContainerInterface;
use Laminas\InputFilter\Input;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\InputFilter\EbsrPackFactory;

/**
 * Class EbsrPackFactoryTest
 * @package OlcsTest\InputFilter
 */
class EbsrPackFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockValidator = m::mock(\Laminas\Validator\AbstractValidator::class);
        $mockValidator->shouldReceive('setOptions');
        $mockSL = m::mock(ContainerInterface::class);
        $mockSL->shouldReceive('get->get')->andReturn($mockValidator);

        $sut = new EbsrPackFactory();

        $service = $sut->__invoke($mockSL, Input::class);
        $this->assertInstanceOf(Input::class, $service);
    }
}
