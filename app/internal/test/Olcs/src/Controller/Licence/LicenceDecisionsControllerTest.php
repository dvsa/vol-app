<?php

namespace OlcsTest\Controller\Licence;

use Mockery as m;

use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

/**
 * Licence Decisions controller tests
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class LicenceDecisionsControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Licence\LicenceDecisionsController');
    }

    public function testGetCurtailAction()
    {
        $id = 69;
        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($id);

        $this->mockService('Helper\LicenceStatus', 'isLicenceCurtailable')
            ->andReturn(true)
            ->shouldReceive('getMessages')
            ->andReturn(
                array(
                    array(
                        'message' => 'a'
                    ),
                    array(
                        'message' => 'b'
                    ),
                    array(
                        'message' => 'c'
                    )
                )
            );

        $form = $this->createMockForm('LicenceStatusDecisionMessages');

        $this->mockService('Helper\Translation', 'translate');

        $this->sut->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isPost')
                    ->andReturn(false)
                    ->getMock()
            );

        $this->sut->shouldReceive('getUrlFromRoute')
            ->with(
                'licence/curtail-licence',
                array(
                    'licence' => $id
                )
            )->andReturn('/licence/69/curtail');

        $form->shouldReceive('setAttribute')
            ->with('action', '/licence/69/curtail');

        $form->shouldReceive('get')
            ->with('messages')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('message')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setValue')
                            ->with('<br><br>')
                            ->getMock()
                    )->getMock()
            );


        $this->sut->shouldReceive('getViewWithLicence')
            ->with(array(
                'form' => $form
            ))
            ->andReturn(m::mock()->shouldReceive('setTemplate')->getMock());

        $this->sut->shouldReceive('renderView');

        $this->sut->curtailAction();
    }
}
