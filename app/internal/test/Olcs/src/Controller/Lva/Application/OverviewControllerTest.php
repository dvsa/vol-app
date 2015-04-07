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
     * @group lva-controllers
     * @group lva-application-overview-controller
     */
    public function testIndexGet()
    {
        $applicationId  = 69;
        $licenceId      = 77;
        $organisationId = 99;

        $applicationData = $this->getStubApplicationData($applicationId, $licenceId, $organisationId);
        $licenceData     = $this->getStubLicenceData($licenceId, $organisationId);

        $trackingData = [
          'addressesStatus'              => null,
          'businessDetailsStatus'        => null,
          'businessTypeStatus'           => null,
          'communityLicencesStatus'      => null,
          'conditionsUndertakingsStatus' => null,
          'convictionsPenaltiesStatus'   => null,
          'createdOn'                    => null,
          'discsStatus'                  => null,
          'financialEvidenceStatus'      => 2,
          'financialHistoryStatus'       => null,
          'id'                           => 1,
          'lastModifiedOn'               => '2015-02-19T15:32:02+0000',
          'licenceHistoryStatus'         => null,
          'operatingCentresStatus'       => null,
          'peopleStatus'                 => null,
          'safetyStatus'                 => null,
          'taxiPhvStatus'                => null,
          'transportManagersStatus'      => null,
          'typeOfLicenceStatus'          => 1,
          'undertakingsStatus'           => null,
          'vehiclesDeclarationsStatus'   => null,
          'vehiclesPsvStatus'            => null,
          'vehiclesStatus'               => null,
          'version'                      => 3,
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
            // 'operatorName'               => 'Foo Ltd.',
            // 'operatorId'                 => 99,
            // 'numberOfLicences'           => 1,
            // 'tradingName'                => 'Foo',
            // 'currentApplications'        => 2,
            // 'applicationCreated'         => '2015-04-06',
            // 'oppositionCount'            => 0,
            // 'licenceStatus'              => Licence::LICENCE_STATUS_NOT_SUBMITTED,
            // 'interimStatus'              => 'Requested (<a href="INTERIM_URL">Interim details</a>)',
            // 'outstandingFees'            => 0,
            // 'licenceStartDate'           => NULL,
            // 'continuationDate'           => NULL,
            // 'numberOfVehicles'           => 0,
            // 'totalVehicleAuthorisation'  => '0 (2)',
            // 'numberOfOperatingCentres'   => '2',
            // 'totalTrailerAuthorisation'  => '0 (2)',
            // 'numberOfIssuedDiscs'        => NULL,
            // 'numberOfCommunityLicences'  => '3',
            // 'openCases'                  => '4',
            // 'currentReviewComplaints'    => NULL,
            // 'previousOperatorName'       => NULL,
            // 'previousLicenceNumber'      => NULL,
            // 'outOfOpposition'            => NULL,
            // 'outOfRepresentation'        => NULL,
            // 'changeOfEntity'             => NULL,
            // 'receivesMailElectronically' => NULL,
            // 'registeredForSelfService'   => NULL,
            'foo' => 'bar',
        ];

        $this->mockService('Helper\ApplicationOverview', 'getViewData')
            ->with($applicationData, $licenceData, 'application')
            ->once()
            ->andReturn($viewData);

        $this->mockRender();

        $view = $this->sut->indexAction();

        $this->assertEquals('pages/application/overview', $view->getTemplate());

        foreach ($viewData as $key => $value) {
            $this->assertEquals($value, $view->getVariable($key), "'$key' not as expected");
        }
    }

    public function testIndexPostValidSave()
    {
        $applicationId = 69;

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

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
        $applicationId = 69;

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $postData = [
            'STUB DATA',
            'form-actions' => [
                'saveAndContinue' => ''
            ],
        ];

        $this->setPost($postData);

        $form = $this->getMockForm();

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

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

        $form = $this->getMockForm();

        $form->shouldReceive('setData')->never();

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

        $this->mockRender();

        $view = $this->sut->indexAction();

        $this->assertEquals('pages/application/overview', $view->getTemplate());
    }

    protected function getMockForm()
    {
        $form = $this->createMockForm('ApplicationOverview');

        $trackingFieldset = m::mock();
        $form->shouldReceive('get')->with('tracking')->andReturn($trackingFieldset);

        $this->mockTcAreaSelect($form);

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

    // @TODO move to trait?
    protected function mockTcAreaSelect($form)
    {
        $tcAreaOptions = [
            'A' => 'Traffic area A',
            'B' => 'Traffic area A',
        ];

        $this->mockEntity('TrafficArea', 'getValueOptions')
            ->andReturn($tcAreaOptions);

        $form->shouldReceive('get')->with('details')->andReturn(
            m::mock()
                ->shouldReceive('get')
                    ->with('leadTcArea')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setValueOptions')
                            ->with($tcAreaOptions)
                            ->getMock()
                    )
                ->getMock()
        );

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
            'niFlag' => 'N',
            'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL],
            'goodsOrPsv' => ['id' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE],
            'createdOn' => '2015-04-06',
            'totAuthVehicles' => 2,
            'totAuthTrailers' => 2,
        ];
    }

    protected function getStubLicenceData($licenceId, $organisationId)
    {
        return [
            'id' => $licenceId,
            'organisation' => [
                'id' => $organisationId,
                'leadTcArea' => ['id' => 'W'],
                'name' => 'Foo Ltd.',
                'licences' => [
                    ['id' => $licenceId]
                ],
            ],
            'status' => ['id' => Licence::LICENCE_STATUS_NOT_SUBMITTED],
            'inForceDate' => null,
            'expiryDate' => null,
            'licenceVehicles' => [],
            'totAuthVehicles' => null,
            'totAuthTrailers' => null,
            'operatingCentres' => [
                ['id' => 1],
                ['id' => 2],
            ],
        ];
    }
}
