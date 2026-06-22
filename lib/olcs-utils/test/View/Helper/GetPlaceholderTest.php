<?php

namespace Dvsa\OlcsTest\Utils\View\Helper;

use Dvsa\Olcs\Utils\View\Helper\GetPlaceholder;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\TestCase;

class GetPlaceholderTest extends TestCase
{
    protected $container;

    protected $sut;

    public function setUp(): void
    {
        $this->container = $this->getMockBuilder(\stdClass::class)->addMethods(['getValue'])->getMock();
    }

    /**
     * @dataProvider asStringProvider
     */
    public function testAsString($value, $expected)
    {
        $this->container->method('getValue')->willReturn($value);

        $this->assertEquals($expected, $this->getService()->asString());
    }

    /**
     * @dataProvider asViewProvider
     */
    public function testAsView($value, $expected)
    {
        $this->container->method('getValue')->willReturn($value);

        $this->assertEquals($expected, $this->getService()->asView());
    }

    /**
     * @dataProvider asObjectProvider
     */
    public function testAsObject($value, $expected)
    {
        $this->container->method('getValue')->willReturn($value);

        $this->assertEquals($expected, $this->getService()->asObject());
    }

    /**
     * @dataProvider asBoolProvider
     */
    public function testAsBool($value, $expected)
    {
        $this->container->method('getValue')->willReturn($value);

        $this->assertEquals($expected, $this->getService()->asBool());
    }

    public function asStringProvider()
    {
        return [
            [
                ['foo'],
                'foo'
            ],
            [
                'foo',
                'foo'
            ],
            [
                [
                    ['foo']
                ],
                null
            ]
        ];
    }

    public function asViewProvider()
    {
        $view = new ViewModel();

        return [
            [
                [$view],
                $view
            ],
            [
                $view,
                $view
            ],
            [
                [
                    [$view]
                ],
                null
            ],
            [
                'foo',
                null
            ]
        ];
    }

    public function asObjectProvider()
    {
        $class = new \stdClass();

        return [
            [
                [$class],
                $class
            ],
            [
                $class,
                $class
            ],
            [
                [
                    [$class]
                ],
                null
            ],
            [
                'foo',
                null
            ]
        ];
    }

    public function asBoolProvider()
    {
        return [
            [
                [true],
                true
            ],
            [
                true,
                true
            ],
            [
                [
                    [true]
                ],
                null
            ],
            [
                'foo',
                null
            ]
        ];
    }

    protected function getService(): GetPlaceholder
    {
        return new GetPlaceholder($this->container);
    }
}
