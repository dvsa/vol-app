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

        $formData = ['tracking' => $trackingData];
        $form
            ->shouldReceive('setData')
            ->with($formData)
            ->andReturnSelf();

        $this->mockRender();

        $this->sut->indexAction();

        $this->assertEquals('overview', $this->view);
    }
}
