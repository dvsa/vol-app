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

    public function testActiveLicenceCheckGetAction()
    {
        $id = 69;
        $decision = 'not-caught';

        $this->sut->shouldReceive('fromRoute')->with('decision', null)->andReturn($decision);
        $this->sut->shouldReceive('fromRoute')->with('licence', null)->andReturn($id);

        $this->mockService('Helper\Translation', 'translate');
        $this->mockService('Helper\LicenceStatus', 'isLicenceActive')->andReturn(true);
        $this->mockService('Helper\LicenceStatus', 'getMessages')
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

    public function testActiveLicenceCheckPostCurtailAndSuspendAction()
    {
        $id = 69;
        $decision = 'curtail';

        $this->setPost([]);

        $this->sut->shouldReceive('fromRoute')->with('decision', null)->andReturn($decision);
        $this->sut->shouldReceive('fromRoute')->with('licence', null)->andReturn($id);

        $this->mockService('Helper\Translation', 'translate');
        $this->mockService('Helper\LicenceStatus', 'isLicenceActive')->andReturn(true);
        $this->mockService('Helper\LicenceStatus', 'getMessages')->andReturn(array())
            ->andReturn(array());

        $form = $this->createMockForm('LicenceStatusDecisionMessages');

        $this->sut->shouldReceive('redirectToRoute')
            ->with(
                'licence/' . $decision . '-licence',
                array(
                    'licence' => $id
                )
            );

        $this->sut->activeLicenceCheckAction();
    }

    public function testActiveLicenceCheckRevokeAction()
    {
        $id = 69;
        $decision = 'revoke';

        $this->sut->shouldReceive('fromRoute')->with('decision', null)->andReturn($decision);
        $this->sut->shouldReceive('fromRoute')->with('licence', null)->andReturn($id);

        $this->mockService('Helper\Translation', 'translate');
        $this->mockService('Helper\LicenceStatus', 'isLicenceActive')->andReturn(false);
        $this->mockService('Helper\LicenceStatus', 'getMessages')->andReturn(array());

        $this->createMockForm('LicenceStatusDecisionMessages');

        $this->sut->shouldReceive('redirectToRoute')
            ->with(
                'licence/' . $decision . '-licence',
                array(
                    'licence' => $id
                )
            );

        $this->sut->activeLicenceCheckAction();
    }

    public function testActiveLicenceCheckRevokeWithMessagesAction()
    {
        $id = 69;
        $decision = 'revoke';

        $this->sut->shouldReceive('fromRoute')->with('decision', null)->andReturn($decision);
        $this->sut->shouldReceive('fromRoute')->with('licence', null)->andReturn($id);

        $this->mockService('Helper\Translation', 'translate');
        $this->mockService('Helper\LicenceStatus', 'isLicenceActive')->andReturn(true);
        $this->mockService('Helper\LicenceStatus', 'getMessages')
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

        $form = $this->createMockForm('LicenceStatusDecisionMessages')->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                    ->shouldReceive('remove')
                    ->with('continue')
                    ->getMock()
            )
            ->shouldReceive('get')
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
            ->andReturn(
                m::mock()
                    ->shouldReceive('setTemplate')
                    ->getMock()
            );

        $this->sut->shouldReceive('renderView');

        $this->sut->activeLicenceCheckAction();
    }

    /**
     * @dataProvider affectNowDataProvider
     */
    public function testAffectNowActions($action, $serviceMethod, $message)
    {
        $licence = 1;

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);
        $this->sut->shouldReceive('isButtonPressed')
            ->with($serviceMethod)
            ->andReturn(true);

        $this->mockService('Helper\LicenceStatus', $serviceMethod);

        $this->sut->shouldReceive('flashMessenger')
            ->andReturn(
                m::mock()
                    ->shouldReceive('addSuccessMessage')
                    ->with($message)
                    ->getMock()
            );

        $this->sut->shouldReceive('redirectToRouteAjax')
            ->with(
                'licence',
                array(
                    'licence' => $licence
                )
            );

        $this->sut->$action();
    }

    /**
     * @dataProvider actionsGetDataProvider
     */
    public function testGetActions($action, $button, $form)
    {
        $licence = 1;

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);
        $this->sut->shouldReceive('isButtonPressed')
            ->with($button)
            ->andReturn(false);

        $form = $this->createMockForm($form);

        $this->sut->shouldReceive('getViewWithLicence')
            ->with(
                array(
                    'form' => $form
                )
            )->andReturn(
                m::mock()
                    ->shouldReceive('setTemplate')
                    ->getMock()
            );

        $this->mockService('Script', 'loadFiles')->with(['forms/licence-decision']);

        $this->sut->shouldReceive('renderView');

        $this->sut->$action();
    }

    /**
     * @dataProvider actionsPostDataProvider
     */
    public function testPostActions($action, $button, $form, $message, $postVars)
    {
        $licence = 1;

        $this->setPost($postVars);

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);
        $this->sut->shouldReceive('isButtonPressed')
            ->with($button)
            ->andReturn(false);

        $form = $this->createMockForm($form);
        $form->shouldReceive('setData')
            ->with($postVars)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postVars);

        $this->mockService('Entity\LicenceStatusRule', 'createStatusForLicence');

        $this->sut->shouldReceive('flashMessenger')
            ->andReturn(
                m::mock()
                    ->shouldReceive('addSuccessMessage')
                    ->with($message)
                    ->getMock()
            );

        $this->sut->shouldReceive('getViewWithLicence')
            ->with(
                array(
                    'form' => $form
                )
            )->andReturn(
                m::mock()
                    ->shouldReceive('setTemplate')
                    ->getMock()
            );

        $this->mockService('Script', 'loadFiles')->with(['forms/licence-decision']);

        $this->sut->shouldReceive('redirectToRouteAjax')
            ->with(
                'licence',
                array(
                    'licence' => $licence
                )
            );

        $this->sut->$action();
    }

    /**
     * @dataProvider actionsPostDataProvider
     */
    public function testPostInvalidActions($action, $button, $form, $message, $postVars)
    {
        $licence = 1;

        $this->setPost($postVars);

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);
        $this->sut->shouldReceive('isButtonPressed')
            ->with($button)
            ->andReturn(false);

        $form = $this->createMockForm($form);
        $form->shouldReceive('setData')
            ->with($postVars)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getData')
            ->andReturn($postVars);

        $this->mockService('Entity\LicenceStatusRule', 'createStatusForLicence');

        $this->sut->shouldReceive('flashMessenger')
            ->andReturn(
                m::mock()
                    ->shouldReceive('addSuccessMessage')
                    ->with($message)
                    ->getMock()
            );

        $this->sut->shouldReceive('getViewWithLicence')
            ->with(
                array(
                    'form' => $form
                )
            )->andReturn(
                m::mock()
                    ->shouldReceive('setTemplate')
                    ->getMock()
            );

        $this->mockService('Script', 'loadFiles')->with(['forms/licence-decision']);

        $this->sut->shouldReceive('redirectToRouteAjax')
            ->with(
                'licence',
                array(
                    'licence' => $licence
                )
            );

        $this->sut->shouldReceive('renderView');

        $this->sut->$action();
    }

    // DATA PROVIDERS

    public function affectNowDataProvider()
    {
        return array(
            array(
                'curtailAction',
                'curtailNow',
                'licence-status.curtailment.message.save.success'
            ),
            array(
                'suspendAction',
                'suspendNow',
                'licence-status.suspension.message.save.success'
            ),
            array(
                'revokeAction',
                'revokeNow',
                'licence-status.revocation.message.save.success'
            )
        );
    }

    public function actionsGetDataProvider()
    {
        return array(
            array(
                'curtailAction',
                'curtailNow',
                'LicenceStatusDecisionCurtail'
            ),
            array(
                'suspendAction',
                'suspendNow',
                'LicenceStatusDecisionSuspend'
            ),
            array(
                'revokeAction',
                'revokeNow',
                'LicenceStatusDecisionRevoke'
            )
        );
    }

    public function actionsPostDataProvider()
    {
        return array(
            array(
                'curtailAction',
                'curtailNow',
                'LicenceStatusDecisionCurtail',
                'licence-status.curtailment.message.save.success',
                array(
                    'licence-decision' => array(
                        'curtailFrom' => null,
                        'curtailTo' => null,
                    )
                )
            ),
            array(
                'suspendAction',
                'suspendNow',
                'LicenceStatusDecisionSuspend',
                'licence-status.suspension.message.save.success',
                array(
                    'licence-decision' => array(
                        'suspendFrom' => null,
                        'suspendTo' => null,
                    )
                )
            ),
            array(
                'revokeAction',
                'revokeNow',
                'LicenceStatusDecisionRevoke',
                'licence-status.revocation.message.save.success',
                array(
                    'licence-decision' => array(
                        'revokeFrom' => null,
                    )
                )
            ),
        );
    }
}
