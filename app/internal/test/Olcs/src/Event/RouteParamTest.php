<?php

declare(strict_types=1);

namespace OlcsTest\Event;

use Olcs\Event\RouteParam;

/**
 * Class RouteParamTest
 * @package OlcsTest\Event
 */
class RouteParamTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSetValue(): void
    {
        $sut = new RouteParam();
        $this->assertNull($sut->getValue());

        $sut->setValue('test');
        $this->assertEquals('test', $sut->getValue());
    }

    public function testGetSetContext(): void
    {
        $sut = new RouteParam();
        $this->assertNull($sut->getContext());

        $sut->setContext(['test' => 'value']);
        $this->assertEquals(['test' => 'value'], $sut->getContext());
    }
}
