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
        $applicationId = 69;

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $form = $this->getMockForm();

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

        $this->mockEntity('ApplicationTracking', 'getTrackingStatuses')
            ->with($applicationId)
            ->andReturn($trackingData);

        $formData = ['tracking' => $trackingData];
        $form
            ->shouldReceive('setData')
            ->with($formData)
            ->andReturnSelf();

        $this->mockRender();

        $this->sut->indexAction();

        $this->assertEquals('overview', $this->view);
    }

    public function testIndexPostValidSave()
    {
        $postData = [
            'id' => '', // we don't use the application id or version (yet!)
            'version' => '',
            'details' => [],
            'tracking' => [
                'id' => 1,
                'version' => 3,
                'typeOfLicenceStatus' => '',
                'businessTypeStatus' => '1',
                'businessDetailsStatus' => '2',
                'addressesStatus' => '3',
                'peopleStatus' => '',
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

        $expectedSaveData = [
                'id' => 1,
                'version' => 3,
                'businessTypeStatus' => '1',
                'businessDetailsStatus' => '2',
                'addressesStatus' => '3',
        ];

        $this->mockService('Entity\ApplicationTracking', 'save')
            ->once()
            ->with($expectedSaveData);

        $this->sut->shouldReceive('addSuccessMessage')->once();
        $this->sut->shouldReceive('reload')->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexPostValidContinue()
    {
        $applicationId = 69;

        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $postData = [
            'id' => '',
            'version' => '',
            'details' => [],
            'tracking' => [
                'id' => 1,
                'version' => 3,
                'typeOfLicenceStatus' => '',
                'businessTypeStatus' => '1',
                'businessDetailsStatus' => '2',
                'addressesStatus' => '3',
                'peopleStatus' => '',
            ],
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

        $this->mockService('Entity\ApplicationTracking', 'save')
            ->once()
            ->with(m::type('array'));

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
        $postData = [
            'id' => '',
            'version' => '',
            'details' => [],
            'tracking' => [
                'id' => 1,
                'version' => 3,
                'typeOfLicenceStatus' => '',
                'businessTypeStatus' => '1',
                'businessDetailsStatus' => '2',
                'addressesStatus' => '3',
                'peopleStatus' => '',
            ],
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
        $postData = [];

        $this->setPost($postData);

        $form = $this->getMockForm();

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $form->shouldReceive('isValid')->once()->andReturn(false);

        $this->mockService('Entity\ApplicationTracking', 'save')->never();

        $applicationId = 69;
        $this->sut->shouldReceive('params')->with('application')->andReturn($applicationId);

        $this->mockRender();

        $this->sut->indexAction();

        $this->assertEquals('overview', $this->view);
    }

    protected function getMockForm()
    {
        $form = $this->createMockForm('ApplicationOverview');

        $trackingFieldset = m::mock();
        $form->shouldReceive('get')->with('tracking')->andReturn($trackingFieldset);

        $sections = [
            'type_of_licence',
            'business_type',
            'business_details',
            'addresses',
            'people',
        ];
        $this->sut->shouldReceive('getAccessibleSections')->once()->andReturn($sections);
        $trackingFieldset
            ->shouldReceive('add')
            ->times(count($sections))
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
}
