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

    public function testActiveLicenceCheckAction()
    {
        $id = 69;
        $decision = 'curtail';

        $this->sut->shouldReceive('fromRoute')->with('decision', null)->andReturn($decision);
        $this->sut->shouldReceive('fromRoute')->with('licence', null)->andReturn($id);

        $this->mockService('Helper\Translation', 'translate');
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
        $form->shouldReceive('setAttribute')
            ->with('action', '/licence/69/active-licence-check/curtail');

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
            ->with(
                array(
                    'form' => $form
                )
            )->andReturn(m::mock()->shouldReceive('setTemplate')->getMock());

        $this->sut->shouldReceive('renderView');

        $this->sut->activeLicenceCheckAction();
    }
}
