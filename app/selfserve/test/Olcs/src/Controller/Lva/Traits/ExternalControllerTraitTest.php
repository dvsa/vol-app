<?php

declare(strict_types=1);

/**
 * External Controller Trait Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace OlcsTest\Controller\Lva\Traits;

use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * External Controller Trait Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ExternalControllerTraitTest extends MockeryTestCase
{
    protected $sut;

    protected function setUp(): void
    {
        $this->sut = m::mock(\OlcsTest\Controller\Lva\Traits\Stubs\ExternalControllerTraitStub::class)
            ->makePartial();
    }

    public function testRenderWithViewModelReturnsViewModel(): void
    {
        $this->sut->shouldReceive('attachCurrentMessages');

        $view = new \Laminas\View\Model\ViewModel();

        $this->assertEquals(
            $view,
            $this->sut->callRender($view)
        );
    }

    public function testRenderWithNormalRequest(): void
    {
        $this->sut->shouldReceive('attachCurrentMessages')
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('handleQuery')
            ->andReturn(
                m::mock()
                ->shouldReceive('getResult')
                ->andReturn(
                    [
                        'inForceDate' => '01/01/2015',
                        'expiryDate' => '01/01/2016',
                        'status' => ['id' => 'lsts_valid'],
                        'licNo' => 'OB1'
                    ]
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('getLicenceId')
            ->andReturn(1)
            ->once()
            ->getMock();

        $view = $this->sut->callRender('my-page');

        $this->assertEquals('layout/layout', $view->getTemplate());
        $this->assertTrue($view->terminate());

        $this->assertEquals(1, $view->count());

        $children = $view->getChildren();

        $this->assertEquals(
            [
                'title' => 'lva.section.title.my-page',
                'form' => null,
                'startDate' => '01/01/2015',
                'renewalDate' => '01/01/2016',
                'status' => 'lsts_valid',
                'licNo' => 'OB1',
                'lva' => 'licence'
            ],
            (array)$children[0]->getVariables()
        );
    }

    public function testRenderWithXmlHttpRequest(): void
    {
        $this->sut->shouldReceive('attachCurrentMessages')
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(true)
                ->getMock()
            )
            ->shouldReceive('handleQuery')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getResult')
                    ->andReturn(
                        [
                            'inForceDate' => '01/01/2015',
                            'expiryDate' => '01/01/2016',
                            'status' => ['id' => 'lsts_valid'],
                            'licNo' => 'OB1'
                        ]
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getLicenceId')
            ->andReturn(1)
            ->once()
            ->getMock();

        $view = $this->sut->callRender('my-page');

        $this->assertEquals('layout/ajax', $view->getTemplate());
        $this->assertTrue($view->terminate());
    }
}
