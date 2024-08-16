<?php

namespace OlcsTest\FormService\Form\Lva\OperatingCentres;

use Laminas\Form\ElementInterface;
use Laminas\InputFilter\InputFilter;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Validator\ValidatorChain;
use Olcs\FormService\Form\Lva\OperatingCentres\ApplicationOperatingCentres;
use Common\Form\Elements\Types\Table;
use Common\FormService\FormServiceManager;
use Common\Service\Table\TableBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Common\Service\Helper\FormHelperService;
use Common\RefData;

/**
 * Application Operating Centres Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOperatingCentresTest extends MockeryTestCase
{
    protected $form;

    /**
     * @var ApplicationOperatingCentres
     */
    protected $sut;

    protected $mockFormHelper;

    protected $tableBuilder;

    public function setUp(): void
    {
        $this->tableBuilder = m::mock(TableBuilder::class);

        $serviceManager = $this->createMock(ServiceLocatorInterface::class);
        $serviceManager
            ->method('get')
            ->with('Table')
            ->willReturn($this->tableBuilder);

        $fsm = m::mock(FormServiceManager::class)->makePartial();
        $fsm->shouldReceive('getServiceLocator')
            ->andReturn($serviceManager);

        $this->form = m::mock(Form::class);

        $lvaApplication = m::mock(\Common\Form\Form::class);
        $lvaApplication->shouldReceive('alterForm')
            ->once()
            ->with($this->form);

        $fsm->setService('lva-application', $lvaApplication);

        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\OperatingCentres')
            ->andReturn($this->form);

        $this->sut = new ApplicationOperatingCentres($this->mockFormHelper, m::mock(\LmcRbacMvc\Service\AuthorizationService::class), $this->tableBuilder, $fsm);
    }

    public function testGetForm()
    {
        $params = [
            'operatingCentres' => [],
            'canHaveSchedule41' => false,
            'canHaveCommunityLicences' => true,
            'isPsv' => false,
            'trafficArea' => null,
            'niFlag' => null,
            'possibleTrafficAreas' => 'POSSIBLE_TRAFFIC_AREAS',
            'possibleEnforcementAreas' => 'POSSIBLE_ENFORCEMENT_AREAS',
            'licenceType' => ['id' => RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            'vehicleType' => ['id' => RefData::APP_VEHICLE_TYPE_MIXED],
            'totAuthLgvVehicles' => 0,
        ];

        $this->mockPopulateFormTable([]);

        $this->mockFormHelper->shouldReceive('getValidator->setMessage')
            ->with('OperatingCentreNoOfOperatingCentres.required', 'required');

        $mockTaElement = m::mock(ElementInterface::class);
        $this->form->shouldReceive('get')->with('dataTrafficArea')->once()->andReturn($mockTaElement);

        $validatorChain = m::mock(ValidatorChain::class);
        $validatorChain->shouldReceive('attach');
        $validatorChain->shouldReceive('getValidators')->andReturn([]);

        $inputFilter = m::mock(InputFilter::class);
        $inputFilter->shouldReceive('get')->andReturnSelf();
        $inputFilter->shouldReceive('getValidatorChain')->andReturn($validatorChain);

        $this->form->shouldReceive('getInputFilter')->andReturn($inputFilter);

        $mockTaElement->shouldReceive('get->setValueOptions')->with('POSSIBLE_TRAFFIC_AREAS')->once();
        $mockTaElement->shouldReceive('remove')->with('trafficAreaSet')->once();

        $mockTaElement->shouldReceive('get->setValueOptions')->with('POSSIBLE_ENFORCEMENT_AREAS')->once();

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
        $table->shouldReceive('removeAction')
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
