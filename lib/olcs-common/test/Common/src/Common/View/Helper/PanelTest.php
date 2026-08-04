<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\Panel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\View\Helper\LinkBack::class)]
final class PanelTest extends MockeryTestCase
{
    /** @var  \Laminas\View\Renderer\RendererInterface */
    private $mockView;

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestInvoke')]
    public function testInvoke($params, $expect, $expected_css_class): void
    {
        $this->mockView = m::mock(\Laminas\View\Renderer\RendererInterface::class)
            ->shouldReceive('render')
            ->withArgs([
                'partials/panel',
                [
                    'theme' => $expected_css_class,
                    'title' => $params['title'],
                    'body' => $params['body']
                ]
            ])
            ->Once()
            ->andReturn('html_string')
            ->getMock();

        $sut = new Panel()
            ->setView($this->mockView);

        $this->assertEquals($expect, $sut->__invoke($params['type'], $params['title'], $params['body']));
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | string)>>
     *
     * @psalm-return list{array{params: array{type: 'success', title: 'title', body: 'body'}, expect: 'html_string', expected_css_class: 'govuk-panel--confirmation'}, array{params: array{type: 'does not exist', title: 'title', body: 'body'}, expect: 'html_string', expected_css_class: ''}}
     */
    public static function dpTestInvoke(): \Iterator
    {
        //  parameter not set, no referer page
        yield [
            'params' => [
                'type' => 'success',
                'title' => 'title',
                'body' => 'body',
            ],
            'expect' => 'html_string',
            'expected_css_class' => 'govuk-panel--confirmation'
        ];
        yield [
            'params' => [
                'type' => 'does not exist',
                'title' => 'title',
                'body' => 'body',
            ],
            'expect' => 'html_string',
            'expected_css_class' => ''
        ];
    }
}
