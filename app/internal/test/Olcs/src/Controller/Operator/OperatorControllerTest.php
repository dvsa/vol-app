<?php

/**
 * Operator controller tests
 */
namespace OlcsTest\Controller\Operator;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * Operator controller tests
 */
class OperatorControllerTest extends MockeryTestCase
{
    protected function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = m::mock('\Olcs\Controller\Operator\OperatorController')
            ->makePartial()->shouldAllowMockingProtectedMethods();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testNewApplicationActionWithGet()
    {
        $mockRequest = $this->mockRequest(false);

        $mockDateHelper = m::mock();
        $mockDateHelper->shouldReceive('getDateObject')
            ->andReturn('DATE');

        $mockForm = m::mock();
        $mockForm->shouldReceive('setData')
            ->with(['receivedDate' => 'DATE']);

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')
            ->with('NewApplication')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $mockRequest);

        $this->sm->setService('Helper\Date', $mockDateHelper);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('renderView')
            ->andReturnUsing(
                function ($view, $title) {
                    return array($view, $title);
                }
            );

        $return = $this->sut->newApplicationAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $return[0]);
        $this->assertEquals('partials/form', $return[0]->getTemplate());
        $this->assertEquals($mockForm, $return[0]->getVariable('form'));
        $this->assertEquals('Create new application', $return[1]);
    }

    public function testNewApplicationActionWithPostWithInvalid()
    {
        $data = ['receivedDate' => 'DATE'];

        $mockRequest = $this->mockRequest(true);
        $mockRequest->shouldReceive('getPost')
            ->andReturn($data);

        $mockForm = m::mock();
        $mockForm->shouldReceive('setData')
            ->with($data)
            ->shouldReceive('isValid')
            ->andReturn(false);

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')
            ->with('NewApplication')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $mockRequest);

        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('renderView')
            ->andReturnUsing(
                function ($view, $title) {
                    return array($view, $title);
                }
            );

        $return = $this->sut->newApplicationAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $return[0]);
        $this->assertEquals('partials/form', $return[0]->getTemplate());
        $this->assertEquals($mockForm, $return[0]->getVariable('form'));
        $this->assertEquals('Create new application', $return[1]);
    }

    /**
     * @group operatorController
     */
    public function testNewApplicationActionWithPostWithValid()
    {
        $this->markTestSkipped();

        $data = ['receivedDate' => 'DATE', 'trafficArea' => 'B'];
        $operator = 1;

        $mockRequest = $this->mockRequest(true);
        $mockRequest->shouldReceive('getPost')
            ->andReturn($data);

        $mockForm = m::mock();
        $mockForm->shouldReceive('setData')
            ->with($data)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($data);

        $mockFormHelper = m::mock();
        $mockFormHelper->shouldReceive('createForm')
            ->with('NewApplication')
            ->andReturn($mockForm)
            ->shouldReceive('setFormActionFromRequest')
            ->with($mockForm, $mockRequest);

        $mockApplicationService = m::mock();
        $mockApplicationService->shouldReceive('createNew')
            ->with($operator, ['receivedDate' => $data['receivedDate']], $data['trafficArea'])
            ->andReturn(['application' => 3, 'licence' => 4]);

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $this->sut->shouldReceive('params')
            ->with('organisation')
            ->andReturn($operator);

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-application/type_of_licence', ['application' => 3])
            ->andReturn('REDIRECT');

        $return = $this->sut->newApplicationAction();

        $this->assertEquals('REDIRECT', $return);
    }

    protected function mockRequest($isPost)
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn($isPost);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        return $mockRequest;
    }

    public function testDisqualifyWithExistingDisqualification()
    {
        $organisation = [
            'name' => 'ORG NAME',
            'disqualifications' => [
                [
                    'isDisqualified' => 'Y',
                    'startDate' => '2015-02-28',
                    'period' => 24,
                    'notes' => 'NOTES',
                    'version' => 12
                ]
            ]
        ];

        $formData = [
            'name' => 'ORG NAME',
            'isDisqualified' => 'Y',
            'startDate' => '2015-02-28',
            'period' => 24,
            'notes' => 'NOTES',
            'version' => 12
        ];

        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockForm = m::mock();

        $this->sut->shouldReceive('params')->with('organisation')->andReturn(1008);
        $this->sut->shouldReceive('getOperator')->with(1008)->andReturn($organisation);

        $mockRequest = $this->mockRequest(false);

        $mockFormHelper->shouldReceive('createForm')->with('Disqualify')->once()->andReturn($mockForm);
        $mockForm->shouldReceive('setData')->with($formData)->once();
        $mockFormHelper->shouldReceive('setFormActionFromRequest')->with($mockForm, $mockRequest)->once();

        $this->sut->shouldReceive('renderView')->once()->andReturn('VIEW');

        $this->assertSame('VIEW', $this->sut->disqualifyAction());
    }

    public function testDisqualifyNoExistingDisqualifications()
    {
        $organisation = [
            'id' => 1008,
            'name' => 'ORG NAME',
            'disqualifications' => []
        ];

        $formData = [
            'name' => 'ORG NAME',
        ];

        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockForm = m::mock();

        $this->sut->shouldReceive('params')->with('organisation')->andReturn(1008);
        $this->sut->shouldReceive('getOperator')->with(1008)->andReturn($organisation);

        $mockRequest = $this->mockRequest(false);

        $mockFormHelper->shouldReceive('createForm')->with('Disqualify')->once()->andReturn($mockForm);
        $mockForm->shouldReceive('setData')->with($formData)->once();
        $mockFormHelper->shouldReceive('setFormActionFromRequest')->with($mockForm, $mockRequest)->once();

        $mockFormHelper->shouldReceive('attachValidator')->once()->andReturnUsing(
            function ($form, $element, $validator) use ($mockForm) {
                $this->assertSame($mockForm, $form);
                $this->assertSame('isDisqualified', $element);
                $this->assertEquals(new \Zend\Validator\Identical('Y'), $validator);
            }
        );

        //$mockForm->shouldReceive('getInputFilter->get->setRequired')->with(false)->once()->andReturn();

        $this->sut->shouldReceive('renderView')->once()->andReturn('VIEW');

        $this->assertSame('VIEW', $this->sut->disqualifyAction());
    }

    public function testDisqualifyStartDateValidator()
    {
        $organisation = [
            'name' => 'ORG NAME',
            'disqualifications' => [
                [
                    'isDisqualified' => 'N',
                    'startDate' => '2015-02-28',
                    'period' => 24,
                    'notes' => 'NOTES',
                    'version' => 12
                ]
            ]
        ];

        $formData = [
            'name' => 'ORG NAME',
            'isDisqualified' => 'N',
            'startDate' => '2015-02-28',
            'period' => 24,
            'notes' => 'NOTES',
            'version' => 12
        ];

        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockForm = m::mock();

        $this->sut->shouldReceive('params')->with('organisation')->andReturn(1008);
        $this->sut->shouldReceive('getOperator')->with(1008)->andReturn($organisation);

        $mockRequest = $this->mockRequest(false);

        $mockFormHelper->shouldReceive('createForm')->with('Disqualify')->once()->andReturn($mockForm);
        $mockForm->shouldReceive('setData')->with($formData)->once();
        $mockFormHelper->shouldReceive('setFormActionFromRequest')->with($mockForm, $mockRequest)->once();

        $mockForm->shouldReceive('getInputFilter->get->setRequired')->with(false)->once()->andReturn();

        $this->sut->shouldReceive('renderView')->once()->andReturn('VIEW');

        $this->assertSame('VIEW', $this->sut->disqualifyAction());
    }

    public function testDisqualifyWithExistingDisqualificationPost()
    {
        $organisation = [
            'id' => 1008,
            'name' => 'ORG NAME',
            'disqualifications' => [
                [
                    'isDisqualified' => 'Y',
                    'startDate' => '2015-02-28',
                    'period' => 24,
                    'notes' => 'NOTES',
                    'version' => 12
                ]
            ]
        ];

        $formData = [
            'name' => 'ORG NAME',
            'isDisqualified' => 'Y',
            'startDate' => '2015-04-28',
            'period' => 241,
            'notes' => 'NOTES2',
            'version' => 13
        ];

        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $mockForm = m::mock();

        $this->sut->shouldReceive('params')->with('organisation')->andReturn(1008);
        $this->sut->shouldReceive('getOperator')->with(1008)->andReturn($organisation);

        $mockRequest = $this->mockRequest(true);
        $mockRequest->shouldReceive('getPost')->with()->once()->andReturn($formData);

        $mockFormHelper->shouldReceive('createForm')->with('Disqualify')->once()->andReturn($mockForm);
        $mockForm->shouldReceive('setData')->with($formData)->once();
        $mockFormHelper->shouldReceive('setFormActionFromRequest')->with($mockForm, $mockRequest)->once();

        $mockForm->shouldReceive('isValid')->with()->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->with()->once()->andReturn($formData);

        // do the save

        $this->sut->shouldReceive('saveDisqualification')->with(
            [
                'name' => 'ORG NAME',
                'isDisqualified' => 'Y',
                'startDate' => '2015-04-28',
                'period' => 241,
                'notes' => 'NOTES2',
                'version' => 13
            ],
            1008,
            $organisation['disqualifications'][0]
        )->once()->andReturn(true);

        $this->sut->shouldReceive('redirect->toRouteAjax')->with('operator', ['organisation' => 1008])->once()
            ->andReturn('VIEW');

        $this->assertSame('VIEW', $this->sut->disqualifyAction());
    }

    public function testGetOperator()
    {
        $response = m::mock();

        $this->sut->shouldReceive('handleQuery')->once()->andReturn($response);
        $response->shouldReceive('isOk')->with()->once()->andReturn(true);
        $response->shouldReceive('getResult')->with()->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->getOperator(198));
    }

    public function testGetOperatorError()
    {
        $response = m::mock();

        $this->sut->shouldReceive('handleQuery')->once()->andReturn($response);
        $response->shouldReceive('isOk')->with()->once()->andReturn(false);

        $this->setExpectedException(\RuntimeException::class);
        $this->sut->getOperator(198);
    }

    public function testSaveDisqualificationCreate()
    {
        $formData = [
            'isDisqualified' => 'Y',
            'period' => 'PERIOD',
            'startDate' => 'START_DATE',
            'notes' => 'NOTES',

        ];
        $disqualification = null;

        $mockFlashMessenger = m::mock();
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        $mockResult = m::mock();
        $this->sut->shouldReceive('handleCommand')->once()->andReturnUsing(
            function ($dto) use ($mockResult) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Command\Disqualification\Create::class, $dto);
                $this->assertSame(
                    [
                        'organisation' => 108,
                        'officerCd' => null,
                        'isDisqualified' => 'Y',
                        'startDate' => 'START_DATE',
                        'period' => 'PERIOD',
                        'notes' => 'NOTES',
                    ],
                    $dto->getArrayCopy()
                );

                return $mockResult;
            }
        );

        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockFlashMessenger->shouldReceive('addSuccessMessage')->with('The disqualification details have been saved')
            ->once();
        $this->assertTrue($this->sut->saveDisqualification($formData, 108, $disqualification));
    }

    public function testSaveDisqualificationCreateError()
    {
        $formData = [
            'isDisqualified' => 'Y',
            'period' => 'PERIOD',
            'startDate' => 'START_DATE',
            'notes' => 'NOTES',

        ];
        $disqualification = null;

        $mockFlashMessenger = m::mock();
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        $mockResult = m::mock();
        $this->sut->shouldReceive('handleCommand')->once()->andReturnUsing(
            function ($dto) use ($mockResult) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Command\Disqualification\Create::class, $dto);
                $this->assertSame(
                    [
                        'organisation' => 108,
                        'officerCd' => null,
                        'isDisqualified' => 'Y',
                        'startDate' => 'START_DATE',
                        'period' => 'PERIOD',
                        'notes' => 'NOTES',
                    ],
                    $dto->getArrayCopy()
                );

                return $mockResult;
            }
        );

        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(false);
        $mockFlashMessenger->shouldReceive('addErrorMessage')->with('unknown-error')
            ->once();

        $this->assertFalse($this->sut->saveDisqualification($formData, 108, $disqualification));
    }

    public function testSaveDisqualificationUpdate()
    {
        $formData = [
            'isDisqualified' => 'Y',
            'period' => 'PERIOD',
            'startDate' => 'START_DATE',
            'notes' => 'NOTES',
            'version' => 234,
        ];
        $disqualification = [
            'id' => 66,
        ];

        $mockFlashMessenger = m::mock();
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        $mockResult = m::mock();
        $this->sut->shouldReceive('handleCommand')->once()->andReturnUsing(
            function ($dto) use ($mockResult) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Command\Disqualification\Update::class, $dto);
                $this->assertSame(
                    [
                        'id' => 66,
                        'version' => 234,
                        'isDisqualified' => 'Y',
                        'startDate' => 'START_DATE',
                        'period' => 'PERIOD',
                        'notes' => 'NOTES',
                    ],
                    $dto->getArrayCopy()
                );

                return $mockResult;
            }
        );

        $mockResult->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockFlashMessenger->shouldReceive('addSuccessMessage')->with('The disqualification details have been saved')
            ->once();
        $this->assertTrue($this->sut->saveDisqualification($formData, 108, $disqualification));
    }
}
