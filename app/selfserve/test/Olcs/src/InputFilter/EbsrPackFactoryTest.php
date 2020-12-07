<?php

namespace OlcsTest\InputFilter;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\InputFilter\EbsrPackFactory;

/**
 * Class EbsrPackFactoryTest
 * @package OlcsTest\InputFilter
 */
class EbsrPackFactoryTest extends MockeryTestCase
{
    public function testCreateService()
    {
        $mockValidator = m::mock('\Laminas\Validator\AbstractValidator');
        $mockValidator->shouldReceive('setOptions');
        $mockSL = m::mock('\Laminas\ServiceManager\ServiceLocatorInterface');
        $mockSL->shouldReceive('get->get')->andReturn($mockValidator);

        $sut = new EbsrPackFactory();

        $service = $sut->createService($mockSL);

        $this->assertInstanceOf('\Laminas\InputFilter\Input', $service);
    }
}
