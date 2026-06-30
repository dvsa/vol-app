<?php

namespace CommonTest\View\Helper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\View\Helper\ReadOnlyActions;
use Laminas\I18n\View\Helper\Translate;
use Laminas\View\Renderer\RendererInterface;

/**
 * ReadOnlyActions Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ReadOnlyActionsTest extends MockeryTestCase
{
    public const BUTTON_LAYOUT = '<input type="submit" name="action" id="%s" class="%s" value="%s">';

    /**
     * @var ReadOnlyActions
     */
    private $sut;

    private $mockView;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockView = m::mock(RendererInterface::class)
            ->shouldReceive('translate')
            ->andReturnUsing(
                static fn($text) => $text . '-translated'
            )
            ->getMock();

        $this->sut = new ReadOnlyActions();
        $this->sut->setView($this->mockView);
    }

    public function testInvokeWithUrl(): void
    {
        $url = 'http://foo.com';
        $label = 'Bar';
        $class = 'large';
        $actions = [
            [
                'url'   => $url,
                'class' => $class,
                'label' => $label,
                'attributes' => [
                    'key' => 'val'
                ]
            ]
        ];

        $expected = sprintf(
            ReadOnlyActions::LINK_WRAPPER,
            $url,
            $class,
            'key="val"',
            $label . '-translated'
        );
        $markup = sprintf(ReadOnlyActions::WRAPPER, $expected);
        $this->assertEquals($markup, $this->sut->__invoke($actions));
    }

    public function testInvokeWithoutUrl(): void
    {
        $label = 'Bar';
        $class = 'large';
        $actions = [
            [
                'class' => $class,
                'label' => $label,
            ]
        ];
        $expected = sprintf(self::BUTTON_LAYOUT, strtolower($label), $class, $label);
        $this->mockView
            ->shouldReceive('formInput')
            ->andReturn($expected)
            ->once()
            ->getMock();

        $markup = sprintf(ReadOnlyActions::WRAPPER, $expected);
        $this->assertEquals($markup, $this->sut->__invoke($actions));
    }
}
