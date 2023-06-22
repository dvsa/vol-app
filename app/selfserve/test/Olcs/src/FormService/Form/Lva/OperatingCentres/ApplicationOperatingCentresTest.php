<?php

namespace OlcsTest\FormService\Form\Lva\OperatingCentres;

use Common\Service\Table\TableFactory;
use Olcs\FormService\Form\Lva\OperatingCentres\ApplicationOperatingCentres;
use Common\Form\Elements\Types\Table;
use Common\FormService\FormServiceInterface;
use Common\FormService\FormServiceManager;
use Common\Service\Table\TableBuilder;
use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Common\Service\Helper\FormHelperService;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\RefData;
use ZfcRbac\Service\AuthorizationService;

/**
 * Application Operating Centres Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOperatingCentresTest extends MockeryTestCase
{
    use ButtonsAlterations;

    protected $form;

    /**
     * @var ApplicationOperatingCentres
     */
    protected $sut;

    protected $mockFormHelper;

    protected $tableBuilder;

    public function setUp(): void
    {

        $this->form = m::mock(Form::class);

        $lvaApplication = m::mock(Form::class);
        $lvaApplication->shouldReceive('alterForm')
            ->once()
            ->with($this->form);

        $fsm = m::mock(FormServiceManager::class);

        $fsm->shouldReceive('get')
            ->with('lva-application')
            ->andReturn($lvaApplication);

        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\OperatingCentres')
            ->andReturn($this->form);

        $this->authService = m::mock(AuthorizationService::class);
        $this->tableBuilder = m::mock(TableFactory::class);

        $this->sut = new ApplicationOperatingCentres($this->mockFormHelper, $this->authService, $this->tableBuilder, $fsm);
    }

    public function testGetForm()
    {
        $params = [
            'operatingCentres' => [],
            'canHaveSchedule41' => false,
            'canHaveCommunityLicences' => true,
            'isPsv' => false,
            'licenceType' => ['id' => RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            'vehicleType' => ['id' => RefData::APP_VEHICLE_TYPE_MIXED],
            'totAuthLgvVehicles' => 0,
        ];

        $tableElement = $this->mockPopulateFormTable([]);

        $this->mockFormHelper->shouldReceive('getValidator->setMessage')
            ->with('OperatingCentreNoOfOperatingCentres.required', 'required');

        $this->mockFormHelper->shouldReceive('remove')
            ->once()
            ->with($this->form, 'dataTrafficArea');

        $tableElement->shouldReceive('get->getTable->removeColumn')
            ->with('noOfComplaints');

        $totCommunityLicences = m::mock();

        $data = m::mock();
        $data->shouldReceive('has')
            ->with('totCommunityLicencesFieldset')
            ->andReturn(true)
            ->shouldReceive('get')
            ->with('totCommunityLicencesFieldset')
            ->andReturn(m::mock()->shouldReceive('get')->with('totCommunityLicences')->andReturn($totCommunityLicences)->getMock());

        $this->form->shouldReceive('get')
            ->with('data')
            ->andReturn($data);

        $this->mockFormHelper->shouldReceive('alterElementLabel')
            ->once()
            ->with($totCommunityLicences, '-external-app', FormHelperService::ALTER_LABEL_APPEND);

        $this->form->shouldReceive('has')
            ->with('dataTrafficArea')
            ->andReturn(true);

        $this->form->shouldReceive('get')
            ->with('dataTrafficArea')
            ->andReturn(
                m::mock()
                ->shouldReceive('remove')
                ->with('enforcementArea')
                ->getMock()
            );

        $this->mockAlterButtons($this->form, $this->mockFormHelper);

        $form = $this->sut->getForm($params);
        $this->assertSame($this->form, $form);
    }

    protected function mockPopulateFormTable($data)
    {
        $columns = [
            'noOfVehiclesRequired' => [
                'title' => 'vehicles',
            ]
        ];

        $table = m::mock(TableBuilder::class);
        $table->shouldReceive('removeColumn')
            ->with('noOfComplaints')
            ->once()
            ->shouldReceive('removeAction')
            ->with('schedule41')
            ->once()
            ->shouldReceive('getColumns')
            ->withNoArgs()
            ->andReturn($columns)
            ->shouldReceive('setColumns')
            ->with(
                [
                    'noOfVehiclesRequired' => [
                        'title' => 'application_operating-centres_authorisation.table.hgvs',
                    ]
                ]
            )
            ->once();

        $tableElement = m::mock(Table::class);
        $tableElement->shouldReceive('getTable')
            ->withNoArgs()
            ->andReturn($table);

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('get')
            ->with('table')
            ->andReturn($tableElement);

        $this->form->shouldReceive('has')
            ->with('table')
            ->andReturnTrue();

        $this->form->shouldReceive('get')
            ->with('table')
            ->andReturn($fieldset);

        $this->tableBuilder->shouldReceive('prepareTable')
            ->with('lva-operating-centres', $data, [])
            ->andReturn($table);

        $this->mockFormHelper->shouldReceive('populateFormTable')
            ->with($fieldset, $table);

        return $tableElement;
    }
}
