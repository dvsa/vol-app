<?php

namespace OlcsTest\Event;

use Olcs\Event\RouteParam;

/**
 * Class RouteParamTest
 * @package OlcsTest\Event
 */
class RouteParamTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSetValue()
    {
        $sut = new RouteParam();
        $this->assertNull($sut->getValue());

        $sut->setValue('test');
        $this->assertEquals('test', $sut->getValue());
    }

    public function testGetSetContext()
    {
        $sut = new RouteParam();
        $this->assertNull($sut->getContext());

        $sut->setContext(['test' => 'value']);
        $this->assertEquals(['test' => 'value'], $sut->getContext());
    }
}
