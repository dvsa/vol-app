<?php

/**
 * Overview Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Application;

use Dvsa\Olcs\Transfer\Command\Application\Overview as OverviewCommand;
use Dvsa\Olcs\Transfer\Query\Application\Overview as OverviewQuery;
use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

/**
 * Overview Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewControllerTest extends AbstractLvaControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Application\OverviewController');
    }

    /**
     * @dataProvider indexGetProvider
     * @param int $applicationId
     * @param array $overviewData
     * @param boolean $shouldRemoveWelshLanguage
     * @group lva-controllers
     * @group lva-application-overview-controller
     */
    public function testIndexGet(
        $applicationId,
        $overviewData,
        $shouldRemoveWelshLanguage
    ) {

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $this->expectQuery(OverviewQuery::class, ['id' => $applicationId], $overviewData);

        $form = $this->getMockForm();

        $formData = [
            'details' => [
                'receivedDate'           => '2015-04-07',
                'targetCompletionDate'   => '2015-05-08',
                'leadTcArea'             => 'W',
                'translateToWelsh'       => 'Y',
                'overrideOppositionDate' => 'Y',
                'version'                => 2,
                'id'                     => $applicationId,
            ],
            'tracking' => $overviewData['applicationTracking'],
        ];
        $form
            ->shouldReceive('setData')
            ->with($formData)
            ->andReturnSelf();

        $viewData = [
            // stub - actual viewdata generation is handled by Helper service
            'foo' => 'bar',
        ];

        $this->mockService('Helper\ApplicationOverview', 'getViewData')
            ->with($overviewData, 'application')
            ->once()
            ->andReturn($viewData);

        $mockFormHelper = $this->getMockFormHelper();

        // Consistency is king...
        if ($shouldRemoveWelshLanguage) {
            $mockFormHelper
                ->shouldReceive('remove')
                ->once()
                ->with($form, 'details->translateToWelsh');
        }

        $this->sut->shouldReceive('url->fromRoute');

        $this->mockRender();

        $view = $this->sut->indexAction();

        $this->assertEquals('sections/application/pages/overview', $view->getTemplate());

        foreach ($viewData as $key => $value) {
            $this->assertEquals($value, $view->getVariable($key), "'$key' not as expected");
        }
    }

    public function indexGetProvider()
    {
        $applicationId  = 69;
        $licenceId      = 77;
        $organisationId = 99;

        return [
            'multiple licences' => [
                $applicationId,
                [
                    'id' => $applicationId,
                    'receivedDate' => '2015-04-07',
                    'targetCompletionDate' => '2015-05-08',
                    'version' => 2,
                    'overrideOoo' => 'Y',
                    'licence' => [
                        'id' => $licenceId,
                        'translateToWelsh' => 'Y',
                        'organisation' => [
                            'id' => $organisationId,
                            'leadTcArea' => ['id' => 'W', 'isWales' => false],
                            'licences' => [
                                ['id' => 123],
                                ['id' => 124],
                            ],
                        ],
                        'trafficArea' => ['id' => 'W', 'isWales' => false],
                        'valueOptions' => [
                            'trafficAreas' => [
                                'A' => 'Traffic area A',
                                'B' => 'Traffic area B',
                            ],
                        ],
                    ],
                    'applicationTracking' => [
                        'id'                           => 1,
                        'version'                      => 3,
                        'addressesStatus'              => null,
                        'businessDetailsStatus'        => null,
                        'businessTypeStatus'           => null,
                    ],
                    'valueOptions' => [
                        'tracking' => [
                            0 => '',
                            1 => 'Accepted',
                            2 => 'Not accepted',
                            3 => 'Not applicable',
                        ],
                    ],
                ],
                true
            ],

            'no active licences' => [
                $applicationId,
                [
                    'id' => $applicationId,
                    'receivedDate' => '2015-04-07',
                    'targetCompletionDate' => '2015-05-08',
                    'version' => 2,
                    'overrideOoo' => 'Y',
                    'licence' => [
                        'id' => $licenceId,
                        'translateToWelsh' => 'Y',
                        'organisation' => [
                            'id' => $organisationId,
                            'leadTcArea' => ['id' => 'W', 'isWales' => true],
                            'licences' => [],
                        ],
                        'trafficArea' => ['id' => 'W', 'isWales' => true],
                        'valueOptions' => [
                            'trafficAreas' => [
                                'A' => 'Traffic area A',
                                'B' => 'Traffic area B',
                            ],
                        ],
                    ],
                    'applicationTracking' => [
                        'id'                           => 1,
                        'version'                      => 3,
                        'addressesStatus'              => null,
                        'businessDetailsStatus'        => null,
                        'businessTypeStatus'           => null,
                    ],
                    'valueOptions' => [
                        'tracking' => [
                            0 => '',
                            1 => 'Accepted',
                            2 => 'Not accepted',
                            3 => 'Not applicable',
                        ],
                    ],
                ],
                false
            ],
        ];
    }

    public function testIndexPostValidSaveSuccess()
    {
        $applicationId  = 69;
        $licenceId      = 77;
        $organisationId = 99;

        $overviewData = $this->getStubOverviewData($applicationId, $licenceId, $organisationId);

        $postData = [
            'details' => [
                'id' => '69',
                'version' => '1',
                'leadTcArea' => 'W',
                'receivedDate' => [
                    'day' => '07',
                    'month' => '04',
                    'year' => '2015'
                ],
                'targetCompletionDate' => [
                    'day' => '08',
                    'month' => '05',
                    'year' => '2015'
                ],
                'overrideOppositionDate' => 'Y',
            ],
            'tracking' => [
                'id' => '1',
                'version' => '3',
                'typeOfLicenceStatus' => '0',
                'businessTypeStatus' => '1',
                'businessDetailsStatus' => '2',
                'addressesStatus' => '3',
                'peopleStatus' => '0',
            ],
            'form-actions' => [
                'save' => ''
            ],
        ];

        $this->setPost($postData);

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $this->expectQuery(OverviewQuery::class, ['id' => $applicationId], $overviewData);

        $form = $this->getMockForm();

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $form->shouldReceive('isValid')->once()->andReturn(true);

        $formData = [
            'details' => [
                'id' => '69',
                'version' => '1',
                'leadTcArea' => 'W',
                'receivedDate' => '2015-04-07',
                'targetCompletionDate' => '2015-05-08',
                'overrideOppositionDate' => 'Y',
            ],
            'tracking' => [
                'id' => '1',
                'version' => '3',
                'typeOfLicenceStatus' => '0',
                'businessTypeStatus' => '1',
                'businessDetailsStatus' => '2',
                'addressesStatus' => '3',
                'peopleStatus' => '0',
            ]
        ];
        $form->shouldReceive('getData')->andReturn($formData);

        $this->sut->shouldReceive('url->fromRoute');

        $expectedCmdData = [
            'id' => $applicationId,
            'version' => '1',
            'leadTcArea' => 'W',
            'receivedDate' => '2015-04-07',
            'targetCompletionDate' => '2015-05-08',
            'tracking' => [
                'id' => '1',
                'version' => '3',
                'typeOfLicenceStatus' => '0',
                'businessTypeStatus' => '1',
                'businessDetailsStatus' => '2',
                'addressesStatus' => '3',
                'peopleStatus' => '0',
            ],
            'overrideOppositionDate' => 'Y',
        ];

        $this->expectCommand(
            OverviewCommand::class,
            $expectedCmdData,
            [
                'id' => [
                    'application' => $applicationId,
                ],
                'messages' => [
                    'application updated',
                ]
            ]
        );

        $this->sut->shouldReceive('addSuccessMessage')->once();
        $this->sut->shouldReceive('reload')->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexPostValidContinue()
    {
        $applicationId  = 69;
        $licenceId      = 77;
        $organisationId = 99;

        $overviewData = $this->getStubOverviewData($applicationId, $licenceId, $organisationId);

        $postData = [
            'form-actions' => [
                'saveAndContinue' => ''
            ],
        ];

        $this->setPost($postData);

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $this->expectQuery(OverviewQuery::class, ['id' => $applicationId], $overviewData);

        $form = $this->getMockForm();

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $form->shouldReceive('isValid')->once()->andReturn(true);

        $formData = [
            'details' => [
                'id' => '69',
                'version' => '1',
                'leadTcArea' => 'W',
                'receivedDate' => '2015-04-07',
                'targetCompletionDate' => '2015-05-08',
                'overrideOppositionDate' => 'Y',
            ],
            'tracking' => [
                'id' => '1',
                'version' => '3',
                'typeOfLicenceStatus' => '0',
                'businessTypeStatus' => '1',
                'businessDetailsStatus' => '2',
                'addressesStatus' => '3',
                'peopleStatus' => '0',
            ]
        ];
        $form->shouldReceive('getData')->andReturn($formData);

        $this->sut->shouldReceive('url->fromRoute');

        $expectedCmdData = [
            'id' => $applicationId,
            'version' => '1',
            'leadTcArea' => 'W',
            'receivedDate' => '2015-04-07',
            'targetCompletionDate' => '2015-05-08',
            'overrideOppositionDate' => 'Y',
            'tracking' => [
                'id' => '1',
                'version' => '3',
                'typeOfLicenceStatus' => '0',
                'businessTypeStatus' => '1',
                'businessDetailsStatus' => '2',
                'addressesStatus' => '3',
                'peopleStatus' => '0',
            ],
        ];

        $this->expectCommand(
            OverviewCommand::class,
            $expectedCmdData,
            [
                'id' => [
                    'application' => $applicationId,
                ],
                'messages' => [
                    'application updated',
                ]
            ]
        );

        $this->sut->shouldReceive('addSuccessMessage')->once();

        $this->sut->shouldReceive('redirect')->andReturn(
            m::mock()
                ->shouldReceive('toRoute')
                    ->with('lva-application/type_of_licence', ['application' => $applicationId])
                    ->andReturn('REDIRECT')
                ->getMock()
        );

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    /**
     * Test index action when business service fails to save, controller should
     * NOT redirect but instead render the form, similarly to a validation failure
     */
    public function testIndexPostValidSaveFails()
    {
        $applicationId  = 69;
        $licenceId      = 77;
        $organisationId = 99;

        $overviewData = $this->getStubOverviewData($applicationId, $licenceId, $organisationId);

        $postData = [
            'form-actions' => [
                'save' => ''
            ],
        ];

        $this->setPost($postData);

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $this->expectQuery(OverviewQuery::class, ['id' => $applicationId], $overviewData);

        $form = $this->getMockForm();

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $this->sut->shouldReceive('url->fromRoute');

        $form->shouldReceive('isValid')->once()->andReturn(true);

        $formData = [
            'details' => [
                'id' => '69',
                'version' => '1',
                'leadTcArea' => 'W',
                'receivedDate' => '2015-04-07',
                'targetCompletionDate' => '2015-05-08',
                'overrideOppositionDate' => 'N',
            ],
            'tracking' => [
                'id' => '1',
                'version' => '3',
                'typeOfLicenceStatus' => '0',
                'businessTypeStatus' => '1',
                'businessDetailsStatus' => '2',
                'addressesStatus' => '3',
                'peopleStatus' => '0',
            ]
        ];
        $form->shouldReceive('getData')->andReturn($formData);

        $this->sut->shouldReceive('url->fromRoute');

        $expectedCmdData = [
            'id' => $applicationId,
            'version' => '1',
            'leadTcArea' => 'W',
            'receivedDate' => '2015-04-07',
            'targetCompletionDate' => '2015-05-08',
            'tracking' => [
                'id' => '1',
                'version' => '3',
                'typeOfLicenceStatus' => '0',
                'businessTypeStatus' => '1',
                'businessDetailsStatus' => '2',
                'addressesStatus' => '3',
                'peopleStatus' => '0',
            ],
            'overrideOppositionDate' => 'N',
        ];

        $this->expectCommand(
            OverviewCommand::class,
            $expectedCmdData,
            [
                'id' => [],
                'messages' => [
                    'failed',
                ]
            ],
            false
        );

        $this->sut->shouldReceive('addErrorMessage')->once();

        $viewData = ['foo' => 'bar'];

        $this->mockService('Helper\ApplicationOverview', 'getViewData')
            ->with($overviewData, 'application')
            ->once()
            ->andReturn($viewData);

        $this->mockRender();

        $view = $this->sut->indexAction();

        $this->assertEquals('sections/application/pages/overview', $view->getTemplate());
    }

    public function testIndexPostCancel()
    {
        $applicationId = 69;

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $postData = [
            'form-actions' => [
                'cancel' => ''
            ],
        ];

        $this->setPost($postData);

        $this->sut->shouldReceive('addSuccessMessage')->once();
        $this->sut->shouldReceive('reload')->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexPostInvalid()
    {
        $applicationId  = 69;
        $licenceId      = 77;
        $organisationId = 99;

        $overviewData = $this->getStubOverviewData($applicationId, $licenceId, $organisationId);

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $postData = [];

        $this->setPost($postData);

        $form = $this->getMockForm();

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $form->shouldReceive('isValid')->once()->andReturn(false);

        $this->expectQuery(OverviewQuery::class, ['id' => $applicationId], $overviewData);

        $this->mockService('Helper\ApplicationOverview', 'getViewData')
            ->with($overviewData, 'application')
            ->once()
            ->andReturn(['VIEWDATA']);

        $this->sut->shouldReceive('url->fromRoute');

        $this->mockRender();

        $view = $this->sut->indexAction();

        $this->assertEquals('sections/application/pages/overview', $view->getTemplate());
    }

    protected function getMockForm()
    {
        $form = $this->createMockForm('ApplicationOverview');

        $trackingFieldset = m::mock();
        $form->shouldReceive('get')->with('tracking')->andReturn($trackingFieldset);

        $tcAreaOptions = [
            'A' => 'Traffic area A',
            'B' => 'Traffic area B',
        ];

        $this->mockEntity('TrafficArea', 'getValueOptions')
            ->andReturn($tcAreaOptions);

        $form->shouldReceive('get')
            ->with('details')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('leadTcArea')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setValueOptions')
                            ->with($tcAreaOptions)
                            ->getMock()
                    )
                    ->shouldReceive('get')
                    ->with('changeOfEntity')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setValue')
                            ->getMock()
                    )
                    ->getMock()
            );

        $sections = [
            'type_of_licence',
            'business_type',
            'business_details',
            'addresses',
            'people',
        ];
        $this->sut->shouldReceive('getAccessibleSections')->once()->andReturn($sections);

        $expectedSectionCount = count($sections);

        $trackingFieldset
            ->shouldReceive('add')
            ->times($expectedSectionCount)
            ->with(m::type('\Common\Form\Elements\InputFilters\SelectEmpty'));

        // assert button label is modified
        $saveButton = m::mock()
            ->shouldReceive('setLabel')
            ->once()
            ->with('Save')
            ->getMock();
        $buttonFieldset = m::mock()
            ->shouldReceive('get')
            ->with('save')
            ->andReturn($saveButton)
            ->getMock();

        $form->shouldReceive('get')->with('form-actions')->andReturn($buttonFieldset);

        return $form;
    }

    protected function getStubOverviewData($applicationId, $licenceId, $organisationId)
    {
         return [
            'id' => $applicationId,
            'receivedDate' => '2015-04-07',
            'targetCompletionDate' => '2015-05-08',
            'overrideOoo' => 'Y',
            'licence' => [
                'id' => $licenceId,
                'organisation' => [
                    'id' => $organisationId,
                    'leadTcArea' => ['id' => 'W', 'isWales' => true],
                    'licences' => [
                        ['id' => 123],
                        ['id' => 124],
                    ],
                ],
                'trafficArea' => ['id' => 'W', 'isWales' => true],
                'valueOptions' => [
                    'trafficAreas' => [
                        'A' => 'Traffic area A',
                        'B' => 'Traffic area B',
                    ],
                ],
            ],
            'version' => 2,
            'valueOptions' => [
                'tracking' => [
                    0 => '',
                    1 => 'Accepted',
                    2 => 'Not accepted',
                    3 => 'Not applicable',
                ],
            ],
            'outOfOppositionDate' => 'OOOD',
            'outOfRepresentationDate' => 'OORD',
        ];
    }
}
