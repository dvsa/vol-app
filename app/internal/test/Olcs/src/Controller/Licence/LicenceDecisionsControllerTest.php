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
        $this->markTestSkipped();
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
                            ->with("")
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

    public function testActiveLicenceCheckPostCurtailAction()
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

    public function testActiveLicenceCheckPostSuspendAction()
    {
        $id = 69;
        $decision = 'suspend';

        $this->setPost([]);

        $this->sut->shouldReceive('fromRoute')->with('decision', null)->andReturn($decision);
        $this->sut->shouldReceive('fromRoute')->with('licence', null)->andReturn($id);
        $this->mockService('Helper\LicenceStatus', 'getMessages')
            ->andReturn(array());

        $this->mockService('Helper\Translation', 'translate');
        $this->mockService('Helper\LicenceStatus', 'isLicenceActive')
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
                            ->with("")
                            ->getMock()
                    )->getMock()
            );

        $this->sut->shouldReceive('getViewWithLicence')
            ->once()
            ->andReturn(
                m::mock()
                    ->shouldReceive('setTemplate')
                    ->once()
                    ->getMock()
            );

        $this->sut->shouldReceive('renderView')
            ->once();

        $this->sut->activeLicenceCheckAction();
    }

    /**
     * @dataProvider affectNowDataProvider
     */
    public function testAffectNowActions($action, $serviceMethod, $message)
    {
        $licence = 1;

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);
        $this->sut->shouldReceive('fromRoute')->with('status', null)->andReturn(null);
        $this->sut->shouldReceive('isButtonPressed')
            ->with('affectImmediate')
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
     * @dataProvider removeDataProvider
     */
    public function testRemoveActions($action, $message)
    {
        $licence = 1;

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);
        $this->sut->shouldReceive('fromRoute')->with('status', null)->andReturn(1);
        $this->sut->shouldReceive('isButtonPressed')
            ->with('remove')
            ->andReturn(true);

        $this->mockService('Entity\LicenceStatusRule', 'removeStatusesForLicence');

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
    public function testGetActions($action, $button, $form, $dateField = null)
    {
        $licence = 1;

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);
        $this->sut->shouldReceive('fromRoute')->with('status', null)->andReturn(1);
        $this->sut->shouldReceive('isButtonPressed')
            ->with($button)
            ->andReturn(false);

        $this->mockService('Entity\LicenceStatusRule', 'getStatusForLicence');

        $form = $this->createMockForm($form);
        $form->shouldReceive('get->remove')
            ->with('remove');

        if ($dateField) {
            $mockField = m::mock();
            $form->shouldReceive('get->get')->andReturn($mockField);
            $this->getMockFormHelper()->shouldReceive('setDefaultDate')->with($mockField);
        }

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
     * @dataProvider actionsPostSuccessDataProvider
     */
    public function testPostActions($action, $button, $form, $message, $postVars)
    {
        $licence = 1;

        $this->setPost($postVars);

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);
        $this->sut->shouldReceive('fromRoute')->with('status', null)->andReturn(null);
        $this->sut->shouldReceive('isButtonPressed')
            ->with($button)
            ->andReturn(false);

        $this->mockService('Entity\LicenceStatusRule', 'getStatusForLicence');

        $form = $this->createMockForm($form)
            ->shouldReceive('get')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->andReturnSelf()
            ->shouldReceive('setData')
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
     * @dataProvider actionsPostSuccessDataProvider
     */
    public function testUpdateActions($action, $button, $form, $message, $postVars)
    {
        $licence = 1;

        $this->setPost($postVars);

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);
        $this->sut->shouldReceive('fromRoute')->with('status', null)->andReturn(1);
        $this->sut->shouldReceive('isButtonPressed')
            ->with($button)
            ->andReturn(false);

        $this->mockService('Entity\LicenceStatusRule', 'getStatusForLicence')
            ->andReturn(
                array(
                    'startDate' => '2015-03-30',
                    'endDate' => '2016-03-30',
                    'version' => 1,
                    'id' => $licence
                )
            );

        $form = $this->createMockForm($form)
            ->shouldReceive('setData')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->andReturnSelf()
            ->shouldReceive('setData')
            ->with($postVars)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postVars);

        $this->mockService('Entity\LicenceStatusRule', 'updateStatusForLicence');

        $this->sut->shouldReceive('flashMessenger')
            ->andReturn(
                m::mock()
                    ->shouldReceive('addSuccessMessage')
                    ->with($message)
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
     * @dataProvider actionsPostFailDataProvider
     */
    public function testPostInvalidActions($action, $button, $form, $postVars, $dateField = null)
    {
        $licence = 1;

        $this->setPost($postVars);

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);
        $this->sut->shouldReceive('fromRoute')->with('status', null)->andReturn(null);
        $this->sut->shouldReceive('isButtonPressed')
            ->with($button)
            ->andReturn(false);

        $actionsFieldset = m::mock()
            ->shouldReceive('remove')
            ->getMock();

        $licenceDecisionFieldset = m::mock()
            ->shouldReceive('remove')
            ->getMock();

        if ($dateField) {
            $mockField = m::mock();
            $licenceDecisionFieldset
                ->shouldReceive('get')
                ->with($dateField)
                ->once()
                ->andReturn($mockField)
                ->getMock();
            $this->getMockFormHelper()
                ->shouldReceive('setDefaultDate')
                    ->once()
                    ->with($mockField)
                    ->andReturn($mockField);
        }

        $form = $this->createMockForm($form);
        $form
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn($actionsFieldset)
            ->shouldReceive('get')
            ->with('licence-decision')
            ->andReturn($licenceDecisionFieldset)
            ->shouldReceive('setData')
            ->with($postVars)
            ->andReturnSelf()
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getData')
            ->andReturn($postVars);

        if ($dateField) {
            $mockField = m::mock();
            $form->shouldReceive('get->get')->with($dateField)->andReturn($mockField);
            $this->getMockFormHelper()->shouldReceive('setDefaultDate')->with($mockField);
        }

        $this->mockService('Entity\LicenceStatusRule', 'createStatusForLicence');

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

    public function testGetResetToValidAction()
    {
        $licence = 1;

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);

        $this->sut->shouldReceive('params')->with('title')->andReturn('');

        $this->createMockForm('GenericConfirmation')
            ->shouldReceive('get')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('messages')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('message')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setValue')
                            ->with('licence-status.reset.message')
                            ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('submit')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setLabel')
                            ->with('licence-status.reset.title')
                            ->getMock()
                    )
                    ->getMock()
            );

        $this->sut->shouldReceive('renderView');

        $this->sut->resetToValidAction();
    }

    public function testPostResetToValidAction()
    {
        $licence = 1;

        $this->setPost([]);

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);

        $this->sut->shouldReceive('params')->with('title')->andReturn('');

        $this->createMockForm('GenericConfirmation')
            ->shouldReceive('get')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->shouldReceive('get')
            ->with('messages')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('message')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setValue')
                            ->with('licence-status.reset.message')
                            ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('submit')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setLabel')
                            ->with('licence-status.reset.title')
                            ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('setData')
            ->shouldReceive('isValid')
            ->andReturn(true);

        $this->mockService('Helper\LicenceStatus', 'resetToValid')
            ->with(1);

        $this->sut->shouldReceive('flashMessenger->addSuccessMessage')
            ->with('licence-status.reset.message.save.success');

        $this->sut->shouldReceive('redirectToRouteAjax')
            ->with('licence', array('licence' => $licence));

        $this->sut->resetToValidAction();
    }

    public function testSurrenderPostAction()
    {
        $licence = 69;
        $postData = [
            'licence-decision' => [
                'surrenderDate' => '2015-03-30',
            ],
        ];

        $this->setPost($postData);

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);

        $form = $this->createMockForm('LicenceStatusDecisionSurrender');
        $form->shouldReceive('get')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->andReturnSelf()
            ->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $this->mockService('Helper\LicenceStatus', 'surrenderNow')
            ->with($licence, '2015-03-30');

        $this->sut->shouldReceive('flashMessenger')
            ->andReturn(
                m::mock()
                    ->shouldReceive('addSuccessMessage')
                    ->with('licence-status.surrender.message.save.success')
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

        $this->sut->surrenderAction();
    }

    public function testTerminatePostAction()
    {
        $licence = 69;
        $postData = [
            'licence-decision' => [
                'terminateDate' => '2015-03-30',
            ],
        ];

        $this->setPost($postData);

        $this->sut->shouldReceive('fromRoute')->with('licence')->andReturn($licence);

        $form = $this->createMockForm('LicenceStatusDecisionTerminate');
        $form->shouldReceive('get')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->andReturnSelf()
            ->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        $this->mockService('Helper\LicenceStatus', 'terminateNow')
            ->with($licence, '2015-03-30');

        $this->sut->shouldReceive('flashMessenger')
            ->andReturn(
                m::mock()
                    ->shouldReceive('addSuccessMessage')
                    ->with('licence-status.terminate.message.save.success')
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

        $this->sut->terminateAction();
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

    public function removeDataProvider()
    {
        return array(
            array(
                'curtailAction',
                'licence-status.curtailment.message.remove.success'
            ),
            array(
                'suspendAction',
                'licence-status.suspension.message.remove.success'
            ),
            array(
                'revokeAction',
                'licence-status.revocation.message.remove.success'
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
            ),
            array(
                'surrenderAction',
                'surrenderNow',
                'LicenceStatusDecisionSurrender',
                'surrenderDate',
            ),
            array(
                'terminateAction',
                'terminateNow',
                'LicenceStatusDecisionTerminate',
                'terminateDate',
            )
        );
    }

    public function actionsPostSuccessDataProvider()
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

    public function actionsPostFailDataProvider()
    {
        return array(
            array(
                'curtailAction',
                'curtailNow',
                'LicenceStatusDecisionCurtail',
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
                array(
                    'licence-decision' => array(
                        'revokeFrom' => null,
                    )
                )
            ),
            array(
                'surrenderAction',
                '', // button press n/a
                'LicenceStatusDecisionSurrender',
                array(
                    'licence-decision' => array(
                        'surrenderDate' => null,
                    )
                ),
                'surrenderDate',
            ),
            array(
                'terminateAction',
                '', // button press n/a
                'LicenceStatusDecisionTerminate',
                array(
                    'licence-decision' => array(
                        'terminateDate' => null,
                    )
                ),
                'terminateDate',
            ),
        );
    }
}
