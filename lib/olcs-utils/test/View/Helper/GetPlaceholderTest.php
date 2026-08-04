<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\View\Helper;

use Dvsa\Olcs\Utils\View\Helper\GetPlaceholder;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\TestCase;

final class GetPlaceholderTest extends TestCase
{
    protected $container;

    public function setUp(): void
    {
        $this->container = $this->createStub(\Laminas\View\Helper\Placeholder\Container::class);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('asStringProvider')]
    public function testAsString($value, $expected)
    {
        $this->container->method('getValue')->willReturn($value);

        $this->assertEquals($expected, $this->getService()->asString());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('asViewProvider')]
    public function testAsView($value, $expected)
    {
        $this->container->method('getValue')->willReturn($value);

        $this->assertEquals($expected, $this->getService()->asView());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('asObjectProvider')]
    public function testAsObject($value, $expected)
    {
        $this->container->method('getValue')->willReturn($value);

        $this->assertEquals($expected, $this->getService()->asObject());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('asBoolProvider')]
    public function testAsBool($value, $expected)
    {
        $this->container->method('getValue')->willReturn($value);

        $this->assertEquals($expected, $this->getService()->asBool());
    }

    public static function asStringProvider(): \Iterator
    {
        yield [
            ['foo'],
            'foo'
        ];
        yield [
            'foo',
            'foo'
        ];
        yield [
            [
                ['foo']
            ],
            null
        ];
    }

    public static function asViewProvider(): \Iterator
    {
        $view = new ViewModel();
        yield [
            [$view],
            $view
        ];
        yield [
            $view,
            $view
        ];
        yield [
            [
                [$view]
            ],
            null
        ];
        yield [
            'foo',
            null
        ];
    }

    public static function asObjectProvider(): \Iterator
    {
        $class = new \stdClass();
        yield [
            [$class],
            $class
        ];
        yield [
            $class,
            $class
        ];
        yield [
            [
                [$class]
            ],
            null
        ];
        yield [
            'foo',
            null
        ];
    }

    public static function asBoolProvider(): \Iterator
    {
        yield [
            [true],
            true
        ];
        yield [
            true,
            true
        ];
        yield [
            [
                [true]
            ],
            null
        ];
        yield [
            'foo',
            null
        ];
    }

    protected function getService(): GetPlaceholder
    {
        return new GetPlaceholder($this->container);
    }
}
