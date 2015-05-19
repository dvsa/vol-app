<?php

/**
 * Overview Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Application;

use OlcsTest\Bootstrap;
use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;
use Common\Service\Entity\LicenceEntityService as Licence;
use Common\BusinessService\Response;

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
     * @param int $licenceId
     * @param int $organisationId
     * @param array $applicationData
     * @param array $licenceData
     * @param array $changeOfEntity
     * @param boolean $shouldRemoveWelshLanguage
     * @group lva-controllers
     * @group lva-application-overview-controller
     */
    public function testIndexGet(
        $applicationId,
        $licenceId,
        $organisationId,
        $applicationData,
        $licenceData,
        $changeOfEntity,
        $shouldRemoveWelshLanguage
    ) {
        $trackingData = [
          'id'                           => 1,
          'version'                      => 3,
          'addressesStatus'              => null,
          'businessDetailsStatus'        => null,
          'businessTypeStatus'           => null,
          // etc.
        ];

        $interimData = [
            'interimStatus' => [
                'id' => 1,
                'description' => 'Requested',
            ],
        ];

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $this->mockEntity('Application', 'getOverview')
            ->once()
            ->with($applicationId)
            ->andReturn($applicationData);

        $this->mockEntity('Licence', 'getExtendedOverview')
            ->once()
            ->with($licenceId)
            ->andReturn($licenceData);

        $this->mockEntity('ApplicationTracking', 'getTrackingStatuses')
            ->with($applicationId)
            ->andReturn($trackingData);

        $form = $this->getMockForm();

        $formData = [
            'details' => [
                'receivedDate'         => '2015-04-07',
                'targetCompletionDate' => '2015-05-08',
                'leadTcArea'           => 'W',
                'translateToWelsh'     => 'Y',
                'version'              => 2,
                'id'                   => $applicationId,
            ],
            'tracking' => $trackingData,
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
            ->with($applicationData, $licenceData, 'application')
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

        $this->assertEquals('pages/application/overview', $view->getTemplate());

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
                $licenceId,
                $organisationId,
                [
                    'id' => $applicationId,
                    'receivedDate' => '2015-04-07',
                    'targetCompletionDate' => '2015-05-08',
                    'licence' => [
                        'id' => $licenceId,
                        'translateToWelsh' => 'Y',
                        'organisation' => [
                            'id' => $organisationId,
                            'leadTcArea' => ['id' => 'W'],
                        ],
                        'trafficArea' => ['id' => 'W'],
                    ],
                    'version' => 2,
                ],
                [
                    'id' => $licenceId,
                    'organisation' => [
                        'id' => $organisationId,
                        'leadTcArea' => ['id' => 'W', 'isWales' => false],
                        'licences' => [
                            ['id' => 123],
                            ['id' => 124],
                        ],
                    ],
                    'trafficArea' => ['id' => 'W', 'isWales' => false],
                ],
                ['Count' => 1, 'Results' => array(['id' => 1])],
                true
            ],

            'no active licences' => [
                $applicationId,
                $licenceId,
                $organisationId,
                [
                    'id' => $applicationId,
                    'receivedDate' => '2015-04-07',
                    'targetCompletionDate' => '2015-05-08',
                    'licence' => [
                        'id' => $licenceId,
                        'translateToWelsh' => 'Y',
                        'organisation' => [
                            'id' => $organisationId,
                            'leadTcArea' => ['id' => 'W'],
                        ],
                        'trafficArea' => ['id' => 'W'],
                    ],
                    'version' => 2,
                ],
                [
                    'id' => $licenceId,
                    'organisation' => [
                        'id' => $organisationId,
                        'leadTcArea' => ['id' => 'W', 'isWales' => true],
                        'licences' => [],
                    ],
                    'trafficArea' => ['id' => 'W', 'isWales' => true],
                ],
                ['Count' => 0, 'Results' => array()],
                false
            ],
        ];
    }

    public function testIndexPostValidSave()
    {
        $applicationId  = 69;
        $licenceId      = 77;
        $organisationId = 99;

        $applicationData = $this->getStubApplicationData($applicationId, $licenceId, $organisationId);
        $licenceData     = $this->getStubLicenceData($licenceId, $organisationId);

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

        $this->mockEntity('Application', 'getOverview')
            ->once()
            ->with($applicationId)
            ->andReturn($applicationData);

        $this->mockEntity('Licence', 'getExtendedOverview')
            ->once()
            ->with($licenceId)
            ->andReturn($licenceData);

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

        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $bsm->setService(
            'Lva\ApplicationOverview',
            m::mock('\Common\BusinessService\BusinessServiceInterface')
                ->shouldReceive('process')
                ->once()
                ->with($formData)
                ->andReturn(new Response(Response::TYPE_SUCCESS))
                ->getMock()
        );
        $this->sm->setService('BusinessServiceManager', $bsm);

        $this->sut->shouldReceive('addSuccessMessage')->once();
        $this->sut->shouldReceive('reload')->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexPostValidContinue()
    {
        $applicationId  = 69;
        $licenceId      = 77;
        $organisationId = 99;

        $applicationData = $this->getStubApplicationData($applicationId, $licenceId, $organisationId);
        $licenceData     = $this->getStubLicenceData($licenceId, $organisationId);

        $postData = [
            'form-actions' => [
                'saveAndContinue' => ''
            ],
        ];

        $this->setPost($postData);

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $this->mockEntity('Application', 'getOverview')
            ->once()
            ->with($applicationId)
            ->andReturn($applicationData);

        $this->mockEntity('Licence', 'getExtendedOverview')
            ->once()
            ->with($licenceId)
            ->andReturn($licenceData);

        $form = $this->getMockForm();

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $form->shouldReceive('isValid')->once()->andReturn(true);

        $formData = ['FORM'];
        $form->shouldReceive('getData')->andReturn($formData);

        $this->sut->shouldReceive('url->fromRoute');

        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $bsm->setService(
            'Lva\ApplicationOverview',
            m::mock('\Common\BusinessService\BusinessServiceInterface')
                ->shouldReceive('process')
                ->once()
                ->with($formData)
                ->andReturn(new Response(Response::TYPE_SUCCESS))
                ->getMock()
        );
        $this->sm->setService('BusinessServiceManager', $bsm);

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

        $applicationData = $this->getStubApplicationData($applicationId, $licenceId, $organisationId);
        $licenceData     = $this->getStubLicenceData($licenceId, $organisationId);

        $postData = [
            'form-actions' => [
                'save' => ''
            ],
        ];

        $this->setPost($postData);

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $this->mockEntity('Application', 'getOverview')
            ->once()
            ->with($applicationId)
            ->andReturn($applicationData);

        $this->mockEntity('Licence', 'getExtendedOverview')
            ->once()
            ->with($licenceId)
            ->andReturn($licenceData);

        $form = $this->getMockForm();

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $this->sut->shouldReceive('url->fromRoute');

        $form->shouldReceive('isValid')->once()->andReturn(true);

        $formData = ['FORM'];
        $form->shouldReceive('getData')->andReturn($formData);

        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $bsm->setService(
            'Lva\ApplicationOverview',
            m::mock('\Common\BusinessService\BusinessServiceInterface')
                ->shouldReceive('process')
                ->once()
                ->with($formData)
                ->andReturn(new Response(Response::TYPE_FAILED))
                ->getMock()
        );
        $this->sm->setService('BusinessServiceManager', $bsm);

        $this->sut->shouldReceive('addErrorMessage')->once();

        $viewData = ['foo' => 'bar'];

        $this->mockService('Helper\ApplicationOverview', 'getViewData')
            ->with($applicationData, $licenceData, 'application')
            ->once()
            ->andReturn($viewData);

        $this->mockRender();

        $view = $this->sut->indexAction();

        $this->assertEquals('pages/application/overview', $view->getTemplate());
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

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $postData = [];

        $this->setPost($postData);

        $form = $this->getMockForm();

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $form->shouldReceive('isValid')->once()->andReturn(false);

        $applicationData = $this->getStubApplicationData($applicationId, $licenceId, $organisationId);
        $this->mockEntity('Application', 'getOverview')
            ->once()
            ->with($applicationId)
            ->andReturn($applicationData);

        $licenceData = $this->getStubLicenceData($licenceId, $organisationId);
        $this->mockEntity('Licence', 'getExtendedOverview')
            ->once()
            ->with($licenceId)
            ->andReturn($licenceData);

        $this->mockService('Helper\ApplicationOverview', 'getViewData')
            ->with($applicationData, $licenceData, 'application')
            ->once()
            ->andReturn(['VIEWDATA']);

        $this->sut->shouldReceive('url->fromRoute');

        $this->mockRender();

        $view = $this->sut->indexAction();

        $this->assertEquals('pages/application/overview', $view->getTemplate());
    }

    protected function getMockForm()
    {
        $form = $this->createMockForm('ApplicationOverview');

        $trackingFieldset = m::mock();
        $form->shouldReceive('get')->with('tracking')->andReturn($trackingFieldset);

        $tcAreaOptions = [
            'A' => 'Traffic area A',
            'B' => 'Traffic area A',
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

        $this->mockEntity('ApplicationTracking', 'getValueOptions')
            ->andReturn(['status_options_array']);

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

    protected function mockTcAreaSelect($form)
    {
        $tcAreaOptions = [
            'A' => 'Traffic area A',
            'B' => 'Traffic area A',
        ];

        $this->mockEntity('TrafficArea', 'getValueOptions')
            ->andReturn($tcAreaOptions);
    }

    protected function getStubApplicationData($applicationId, $licenceId, $organisationId)
    {

        return [
            'id' => $applicationId,
            'receivedDate' => '2015-04-07',
            'targetCompletionDate' => '2015-05-08',
            'licence' => [
                'id' => $licenceId,
                'organisation' => [
                    'id' => $organisationId,
                    'leadTcArea' => ['id' => 'W'],
                ],
            ],
            'version' => 2,
        ];
    }

    protected function getStubLicenceData($licenceId, $organisationId)
    {
        return [
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
        ];
    }
}
