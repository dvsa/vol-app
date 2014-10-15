<?php

namespace OlcsTest\InputFilter;

use Olcs\InputFilter\EbsrPackFactory;
use Mockery as m;

/**
 * Class EbsrPackFactoryTest
 * @package OlcsTest\InputFilter
 */
class EbsrPackFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $mockValidator = m::mock('\Zend\Validator\AbstractValidator');
        $mockValidator->shouldReceive('setOptions');
        $mockSL = m::mock('\Zend\ServiceManager\ServiceLocatorInterface');
        $mockSL->shouldReceive('get->get')->andReturn($mockValidator);

        $sut = new EbsrPackFactory();

        $service = $sut->createService($mockSL);

        $this->assertInstanceOf('\Zend\InputFilter\Input', $service);
    }
}
