<?php

namespace OlcsTest\Controller;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Form\Form;
use Laminas\View\HelperPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * DisqualifyController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DisqualifyControllerTest extends MockeryTestCase
{
    protected $sut;

    protected function setUp(): void
    {
        $this->mockScriptFactory = m::mock(ScriptFactory::class);
        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockTableFactory = m::mock(TableFactory::class);
        $this->mockViewHelperManager = m::mock(HelperPluginManager::class);
        $this->mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $this->sut = m::mock(\Olcs\Controller\DisqualifyController::class, [
            $this->mockScriptFactory,
            $this->mockFormHelper,
            $this->mockTableFactory,
            $this->mockViewHelperManager,
            $this->mockFlashMessengerHelper,
        ])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testIndexActionNoParam()
    {
        $this->sut->shouldReceive('params->fromRoute')->with('person')->once()->andReturn(false);
        $this->sut->shouldReceive('params->fromRoute')->with('organisation')->once()->andReturn(false);

        $this->expectException(\RuntimeException::class);

        $this->sut->indexAction();
    }

    public function testIndexActionPerson()
    {
        $data = [
            'personCdId' => 91,
            'id' => null,
        ];

        $this->sut->shouldReceive('params->fromRoute')->with('person')->once()->andReturn(12);
        $this->sut->shouldReceive('params->fromRoute')->with('organisation')->once()->andReturn(false);

        $this->sut->shouldReceive('getPerson')->with(12)->once()->andReturn($data);

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->with()->twice()->andReturn(false);

        $this->sut->shouldReceive('getRequest')->with()->once()->andReturn($mockRequest);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('setData')->with($data)->once();

        $this->mockFormHelper->shouldReceive('createForm')->with('Disqualify')->once()->andReturn($mockForm);
        $this->mockFormHelper->shouldReceive('setFormActionFromRequest')->with($mockForm, $mockRequest)->once();
        $this->mockFormHelper->shouldReceive('attachValidator')
            ->with($mockForm, 'isDisqualified', m::type(\Laminas\Validator\Identical::class))->once();

        $this->sut->shouldReceive('renderView')->once()->andReturn('RENDERED');

        $this->assertSame('RENDERED', $this->sut->indexAction());
    }

    public function testIndexActionPersonPost()
    {
        $data = [
            'id' => 120,
            'isDisqualified' => 'N',
        ];

        $postData = [
            'isDisqualified' => 'N',
        ];

        $this->sut->shouldReceive('params->fromRoute')->with('person')->once()->andReturn(12);
        $this->sut->shouldReceive('params->fromRoute')->with('organisation')->once()->andReturn(false);

        $this->sut->shouldReceive('getPerson')->with(12)->once()->andReturn($data);

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->with()->twice()->andReturn(true);
        $mockRequest->shouldReceive('getPost')->with()->once()->andReturn($postData);

        $this->sut->shouldReceive('getRequest')->with()->once()->andReturn($mockRequest);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('setData')->with($postData)->once();
        $mockForm->shouldReceive('getInputFilter->get->setRequired')->with(false)->once();
        $mockForm->shouldReceive('isValid')->with()->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->with()->once()->andReturn(['FORM_DATA']);

        $this->mockFormHelper->shouldReceive('createForm')->with('Disqualify')->once()->andReturn($mockForm);
        $this->mockFormHelper->shouldReceive('setFormActionFromRequest')->with($mockForm, $mockRequest)->once();

        $this->sut->shouldReceive('saveDisqualification')->once()->with(
            ['FORM_DATA'],
            120,
            12,
            0
        )->andReturn(true);

        $this->sut->shouldReceive('closeAjax')->once()->andReturn('RESPONSE');

        $this->assertSame('RESPONSE', $this->sut->indexAction());
    }

    public function testIndexActionPersonPostValidationError()
    {
        $data = [
            'personCdId' => 91,
            'id' => 12,
            'isDisqualified' => 'N',
        ];

        $postData = [
            'isDisqualified' => 'N',
        ];

        $this->sut->shouldReceive('params->fromRoute')->with('person')->once()->andReturn(12);
        $this->sut->shouldReceive('params->fromRoute')->with('organisation')->once()->andReturn(false);

        $this->sut->shouldReceive('getPerson')->with(12)->once()->andReturn($data);

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->with()->twice()->andReturn(true);
        $mockRequest->shouldReceive('getPost')->with()->once()->andReturn($postData);

        $this->sut->shouldReceive('getRequest')->with()->once()->andReturn($mockRequest);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('setData')->with($postData)->once();
        $mockForm->shouldReceive('getInputFilter->get->setRequired')->with(false)->once();
        $mockForm->shouldReceive('isValid')->with()->once()->andReturn(false);

        $this->mockFormHelper->shouldReceive('createForm')->with('Disqualify')->once()->andReturn($mockForm);
        $this->mockFormHelper->shouldReceive('setFormActionFromRequest')->with($mockForm, $mockRequest)->once();

        $this->sut->shouldReceive('renderView')->once()->andReturn('RESPONSE');

        $this->assertSame('RESPONSE', $this->sut->indexAction());
    }

    public function testIndexActionOrganisation()
    {
        $data = [
            'id' => null,
        ];

        $this->sut->shouldReceive('params->fromRoute')->with('person')->once()->andReturn(false);
        $this->sut->shouldReceive('params->fromRoute')->with('organisation')->once()->andReturn(191);

        $this->sut->shouldReceive('getOrganisation')->with(191)->once()->andReturn($data);

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->with()->twice()->andReturn(false);

        $this->sut->shouldReceive('getRequest')->with()->once()->andReturn($mockRequest);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('setData')->with($data)->once();

        $this->mockFormHelper->shouldReceive('createForm')->with('Disqualify')->once()->andReturn($mockForm);
        $this->mockFormHelper->shouldReceive('setFormActionFromRequest')->with($mockForm, $mockRequest)->once();
        $this->mockFormHelper->shouldReceive('attachValidator')
            ->with($mockForm, 'isDisqualified', m::type(\Laminas\Validator\Identical::class))->once();

        $this->sut->shouldReceive('renderView')->once()->andReturn('RENDERED');

        $this->assertSame('RENDERED', $this->sut->indexAction());
    }

    public function testGetOrganisationWithDisqualification()
    {
        $organisation = [
            'name' => 'ACME Ltd',
            'disqualifications' => [
                [
                    'isDisqualified' => 'X',
                    'startDate' => '2015-08-12',
                    'period' => 134,
                    'notes' => 'NOTES',
                    'id' => 234,
                    'version' => 334,
                ]
            ]
        ];

        $mockResponse = m::mock(\Common\Service\Cqrs\Response::class);

        $this->sut->shouldReceive('handleQuery')->once()->andReturnUsing(
            function ($dto) use ($mockResponse) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\Organisation\Organisation::class, $dto);
                $this->assertSame(512, $dto->getId());
                return $mockResponse;
            }
        );

        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResponse->shouldReceive('getResult')->with()->once()->andReturn($organisation);

        $this->assertSame(
            [
                'name' => 'ACME Ltd',
                'id' => 234,
                'isDisqualified' => 'X',
                'startDate' => '2015-08-12',
                'period' => 134,
                'notes' => 'NOTES',
                'version' => 334,
            ],
            $this->sut->getOrganisation(512)
        );
    }

    public function testGetOrganisationWithOutDisqualification()
    {
        $organisation = [
            'name' => 'ACME Ltd',
            'disqualifications' => [
            ]
        ];

        $mockResponse = m::mock(\Common\Service\Cqrs\Response::class);

        $this->sut->shouldReceive('handleQuery')->once()->andReturnUsing(
            function ($dto) use ($mockResponse) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\Organisation\Organisation::class, $dto);
                $this->assertSame(512, $dto->getId());
                return $mockResponse;
            }
        );

        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResponse->shouldReceive('getResult')->with()->once()->andReturn($organisation);

        $this->assertSame(
            [
                'name' => 'ACME Ltd',
                'id' => null,
            ],
            $this->sut->getOrganisation(512)
        );
    }

    public function testGetOrganisationError()
    {
        $mockResponse = m::mock(\Common\Service\Cqrs\Response::class);

        $this->sut->shouldReceive('handleQuery')->once()->andReturnUsing(
            function ($dto) use ($mockResponse) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\Organisation\Organisation::class, $dto);
                $this->assertSame(512, $dto->getId());
                return $mockResponse;
            }
        );

        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->sut->getOrganisation(512);
    }

    public function testGetPersonError()
    {
        $mockResponse = m::mock(\Common\Service\Cqrs\Response::class);

        $this->sut->shouldReceive('handleQuery')->once()->andReturnUsing(
            function ($dto) use ($mockResponse) {
                $this->assertSame(512, $dto->getId());
                return $mockResponse;
            }
        );

        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->sut->getPerson(512);
    }

    public function testGetPersonWithDisqualification()
    {
        $person = [
            'forename' => 'Bob',
            'familyName' => 'Smith',
            'disqualifications' => [
                [
                    'isDisqualified' => 'X',
                    'startDate' => '2015-08-12',
                    'period' => 134,
                    'notes' => 'NOTES',
                    'id' => 234,
                    'version' => 334,
                ]
            ]
        ];

        $mockResponse = m::mock(\Common\Service\Cqrs\Response::class);

        $this->sut->shouldReceive('handleQuery')->once()->andReturnUsing(
            function ($dto) use ($mockResponse) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\Person\Person::class, $dto);
                $this->assertSame(512, $dto->getId());
                return $mockResponse;
            }
        );

        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResponse->shouldReceive('getResult')->with()->once()->andReturn($person);

        $this->assertSame(
            [
                'name' => 'Bob Smith',
                'id' => 234,
                'isDisqualified' => 'X',
                'startDate' => '2015-08-12',
                'period' => 134,
                'notes' => 'NOTES',
                'version' => 334,
            ],
            $this->sut->getPerson(512)
        );
    }

    public function testGetPersonWithOutDisqualification()
    {
        $person = [
            'forename' => 'Bob',
            'familyName' => 'Smith',
            'contactDetails' => [
                [
                    'id' => 34,
                    'disqualifications' => [
                    ]
                ]
            ]
        ];

        $mockResponse = m::mock(\Common\Service\Cqrs\Response::class);

        $this->sut->shouldReceive('handleQuery')->once()->andReturnUsing(
            function ($dto) use ($mockResponse) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\Person\Person::class, $dto);
                $this->assertSame(512, $dto->getId());
                return $mockResponse;
            }
        );

        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResponse->shouldReceive('getResult')->with()->once()->andReturn($person);

        $this->assertSame(
            [
                'name' => 'Bob Smith',
                'id' => null,
            ],
            $this->sut->getPerson(512)
        );
    }

    public function testSaveDisqualificationCreateForPerson()
    {
        $formData = [
            'isDisqualified' => 'X',
            'period' => 23,
            'startDate' => '2015-08-12',
            'notes' => 'NOTES',
        ];

        $mockResponse = m::mock(\Common\Service\Cqrs\Response::class);

        $this->sut->shouldReceive('handleCommand')->once()->andReturnUsing(
            function ($dto) use ($mockResponse) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Command\Disqualification\Create::class, $dto);
                $this->assertSame(
                    [
                        'organisation' => null,
                        'person' => 642,
                        'isDisqualified' => 'X',
                        'startDate' => '2015-08-12',
                        'period' => 23,
                        'notes' => 'NOTES',
                    ],
                    $dto->getArrayCopy()
                );
                return $mockResponse;
            }
        );

        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);

        $this->mockFlashMessengerHelper->shouldReceive('addSuccessMessage')->once();

        $this->sut->saveDisqualification($formData, null, 642, null);
    }

    public function testSaveDisqualificationCreateForOrganisation()
    {
        $formData = [
            'isDisqualified' => 'X',
            'period' => 23,
            'startDate' => '2015-08-12',
            'notes' => 'NOTES',
        ];

        $mockResponse = m::mock(\Common\Service\Cqrs\Response::class);

        $this->sut->shouldReceive('handleCommand')->once()->andReturnUsing(
            function ($dto) use ($mockResponse) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Command\Disqualification\Create::class, $dto);
                $this->assertSame(
                    [
                        'organisation' => 634,
                        'person' => null,
                        'isDisqualified' => 'X',
                        'startDate' => '2015-08-12',
                        'period' => 23,
                        'notes' => 'NOTES',
                    ],
                    $dto->getArrayCopy()
                );
                return $mockResponse;
            }
        );

        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);

        $this->mockFlashMessengerHelper->shouldReceive('addSuccessMessage')->once();

        $this->sut->saveDisqualification($formData, null, null, 634);
    }

    public function testSaveDisqualificationUpdate()
    {
        $formData = [
            'isDisqualified' => 'X',
            'period' => 23,
            'startDate' => '2015-08-12',
            'notes' => 'NOTES',
            'version' => 435,
        ];

        $mockResponse = m::mock(\Common\Service\Cqrs\Response::class);

        $this->sut->shouldReceive('handleCommand')->once()->andReturnUsing(
            function ($dto) use ($mockResponse) {
                $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Command\Disqualification\Update::class, $dto);
                $this->assertSame(
                    [
                        'id' => 12,
                        'version' => 435,
                        'isDisqualified' => 'X',
                        'startDate' => '2015-08-12',
                        'period' => 23,
                        'notes' => 'NOTES',
                    ],
                    $dto->getArrayCopy()
                );
                return $mockResponse;
            }
        );

        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);

        $this->mockFlashMessengerHelper->shouldReceive('addSuccessMessage')->once();

        $this->sut->saveDisqualification($formData, 12, 642, null);
    }

    public function testSaveDisqualificationError()
    {
        $formData = [
            'isDisqualified' => 'X',
            'period' => 23,
            'startDate' => '2015-08-12',
            'notes' => 'NOTES',
            'version' => 435,
        ];

        $mockResponse = m::mock(\Common\Service\Cqrs\Response::class);

        $this->sut->shouldReceive('handleCommand')->once()->andReturn($mockResponse);

        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(false);

        $this->mockFlashMessengerHelper->shouldReceive('addErrorMessage')->with('unknown-error')->once();

        $this->sut->saveDisqualification($formData, 12, 642, null);
    }

    public function testCloseAjaxOperator()
    {
        $this->sut->shouldReceive('params->fromRoute')->with('person')->once()->andReturn(null);

        $this->sut->shouldReceive('redirect->toRouteAjax')->with('operator', [], [], true)->once()
            ->andReturn('RESPONSE');

        $this->assertSame('RESPONSE', $this->sut->closeAjax());
    }

    public function testCloseAjaxPersonOperator()
    {
        $this->sut->shouldReceive('params->fromRoute')->with('person')->once()->andReturn(12);
        $this->sut->shouldReceive('params->fromRoute')->with('organisation')->once()->andReturn(912);

        $this->sut->shouldReceive('redirect->toRouteAjax')->with('operator/people', [], [], true)->once()
            ->andReturn('RESPONSE');

        $this->assertSame('RESPONSE', $this->sut->closeAjax());
    }

    public function testCloseAjaxPersonLicence()
    {
        $this->sut->shouldReceive('params->fromRoute')->with('person')->once()->andReturn(12);
        $this->sut->shouldReceive('params->fromRoute')->with('organisation')->once()->andReturn(false);
        $this->sut->shouldReceive('params->fromRoute')->with('licence')->once()->andReturn(912);

        $this->sut->shouldReceive('redirect->toRouteAjax')->with('lva-licence/people', [], [], true)->once()
            ->andReturn('RESPONSE');

        $this->assertSame('RESPONSE', $this->sut->closeAjax());
    }

    public function testCloseAjaxPersonApplication()
    {
        $this->sut->shouldReceive('params->fromRoute')->with('person')->once()->andReturn(12);
        $this->sut->shouldReceive('params->fromRoute')->with('organisation')->once()->andReturn(false);
        $this->sut->shouldReceive('params->fromRoute')->with('licence')->once()->andReturn(false);
        $this->sut->shouldReceive('params->fromRoute')->with('application')->once()->andReturn(912);

        $this->sut->shouldReceive('redirect->toRouteAjax')->with('lva-application/people', [], [], true)->once()
            ->andReturn('RESPONSE');

        $this->assertSame('RESPONSE', $this->sut->closeAjax());
    }

    public function testCloseAjaxPersonVariation()
    {
        $this->sut->shouldReceive('params->fromRoute')->with('person')->once()->andReturn(12);
        $this->sut->shouldReceive('params->fromRoute')->with('organisation')->once()->andReturn(false);
        $this->sut->shouldReceive('params->fromRoute')->with('licence')->once()->andReturn(false);
        $this->sut->shouldReceive('params->fromRoute')->with('application')->once()->andReturn(false);
        $this->sut->shouldReceive('params->fromRoute')->with('variation')->twice()->andReturn(912);

        $this->sut->shouldReceive('redirect->toRouteAjax')->with('lva-variation/people', ['application' => 912])->once()
            ->andReturn('RESPONSE');

        $this->assertSame('RESPONSE', $this->sut->closeAjax());
    }

    public function testCloseAjaxError()
    {
        $this->sut->shouldReceive('params->fromRoute')->with('person')->once()->andReturn(12);
        $this->sut->shouldReceive('params->fromRoute')->with('organisation')->once()->andReturn(false);
        $this->sut->shouldReceive('params->fromRoute')->with('licence')->once()->andReturn(false);
        $this->sut->shouldReceive('params->fromRoute')->with('application')->once()->andReturn(false);
        $this->sut->shouldReceive('params->fromRoute')->with('variation')->once()->andReturn(false);

        $this->expectException(\RuntimeException::class);

        $this->sut->closeAjax();
    }
}
