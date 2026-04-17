<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\InputFilter;

use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class InputTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\InputFilter
 */
class InputTest extends TestCase
{
    public function testGetValue(): void
    {
        $value = 'raw';
        $filtered = 'filtered';

        $sut = new Input();

        $mockFilterChain = m::mock(\Laminas\Filter\FilterChain::class);
        $mockFilterChain->shouldReceive('filter')->once()->with($value)->andReturn($filtered);
        $sut->setFilterChain($mockFilterChain);

        $sut->setValue($value);
        $this->assertEquals($filtered, $sut->getValue());

        //assert only filtered once
        $sut->getValue();
    }

    public function testSetValueResetsFilter(): void
    {
        $value = 'raw';
        $value2 = 'raw2';
        $filtered = 'filtered';
        $filtered2 = 'filtered2';

        $sut = new Input();

        $mockFilterChain = m::mock(\Laminas\Filter\FilterChain::class);
        $mockFilterChain->shouldReceive('filter')->once()->with($value)->andReturn($filtered);
        $mockFilterChain->shouldReceive('filter')->once()->with($value2)->andReturn($filtered2);
        $sut->setFilterChain($mockFilterChain);

        $returned1 = $sut->setValue($value);
        $this->assertInstanceOf(Input::class, $returned1);
        $this->assertEquals($filtered, $sut->getValue());

        $returned2 = $sut->setValue($value2);
        $this->assertInstanceOf(Input::class, $returned2);
        $this->assertEquals($filtered2, $sut->getValue());
    }
}
