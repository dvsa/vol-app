<?php

declare(strict_types=1);

namespace CommonTest\Common\FormService\Form\Lva\OperatingCentres;

use Common\Form\Elements\Types\Table;
use Common\Form\Elements\Validators\TableRequiredValidator;
use Common\FormService\Form\Lva\OperatingCentres\VariationOperatingCentres;
use Common\FormService\FormServiceManager;
use Common\FormService\Form\Lva\Variation as VariationFormService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Laminas\Form\ElementInterface;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\ValidatorChain;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Common\Service\Helper\FormHelperService;
use Common\RefData;
use LmcRbacMvc\Service\AuthorizationService;

class VariationOperatingCentresTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $authService;
    public $rowsInput;
    public $tableElement;
    protected $form;

    /**
     * @var VariationOperatingCentres
     */
    protected $sut;

    protected $mockFormHelper;

    protected $tableBuilder;

    protected $translator;

    private $inputFilter;

    private $validatorChain;

    #[\Override]
    protected function setUp(): void
    {
        $this->tableBuilder = m::mock(TableFactory::class);
        $this->authService = m::mock(AuthorizationService::class);
        $this->translator = m::mock(TranslationHelperService::class);

        $fsm = m::mock(FormServiceManager::class)->makePartial();

        $validators = [
            0 => [
                'instance' => m::mock(TableRequiredValidator::class),
            ],
        ];

        $this->validatorChain = m::mock(ValidatorChain::class);
        $this->validatorChain->expects('getValidators')->andReturn($validators);
        $this->rowsInput = m::mock(Input::class);
        $this->rowsInput->expects('getValidatorChain')->withNoArgs()->andReturn($this->validatorChain);

        $this->tableElement = m::mock(Table::class);
        $this->tableElement->expects('get')->with('rows')->andReturn($this->rowsInput);

        $this->inputFilter = m::mock(InputFilter::class);
        $this->inputFilter->expects('get')->with('table')->andReturn($this->tableElement);

        $this->form = m::mock(Form::class);
        $this->form->expects('getInputFilter')->withNoArgs()->andReturn($this->inputFilter);

        $lvaVariation = m::mock(VariationFormService::class);
        $lvaVariation->shouldReceive('alterForm')
            ->once()
            ->with($this->form);

        $fsm->setService('lva-variation', $lvaVariation);

        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\OperatingCentres')
            ->andReturn($this->form);

        $this->sut = new VariationOperatingCentres($this->mockFormHelper, $this->authService, $this->tableBuilder, $fsm, $this->translator);
    }

    public function testGetForm(): void
    {
        $params = [
            'operatingCentres' => [],
            'canHaveSchedule41' => false,
            'canHaveCommunityLicences' => true,
            'isPsv' => false,
            'licence' => [
                'totAuthHgvVehicles' => 11,
                'totAuthLgvVehicles' => 10,
                'totAuthTrailers' => 12
            ],
            'licenceType' => ['id' => RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            'vehicleType' => ['id' => RefData::APP_VEHICLE_TYPE_MIXED],
            'totAuthLgvVehicles' => 0,
        ];

        $this->mockPopulateFormTable([]);

        $this->mockFormHelper->shouldReceive('getValidator->setMessage')
            ->with('OperatingCentreNoOfOperatingCentres.required', 'required');

        $this->mockFormHelper->shouldReceive('remove')
            ->once()
            ->with($this->form, 'dataTrafficArea');

        $this->translator->shouldReceive('translateReplace')
            ->with('current-authorisation-hint', [10])
            ->andReturn('current-authorisation-hint-10')
            ->shouldReceive('translateReplace')
            ->with('current-authorisation-hint', [11])
            ->andReturn('current-authorisation-hint-11')
            ->shouldReceive('translateReplace')
            ->with('current-authorisation-hint', [12])
            ->andReturn('current-authorisation-hint-12');

        $data = m::mock(ElementInterface::class);
        $data->shouldReceive('has')
            ->with('totAuthLgvVehiclesFieldset')
            ->andReturn(true)
            ->shouldReceive('has')
            ->with('totAuthHgvVehiclesFieldset')
            ->andReturn(true)
            ->shouldReceive('has')
            ->with('totAuthTrailersFieldset')
            ->andReturn(true)
            ->shouldReceive('has')
            ->with('totCommunityLicencesFieldset')
            ->andReturn(true)
            ->shouldReceive('get')
            ->with('totAuthLgvVehiclesFieldset')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('totAuthLgvVehicles')
                    ->andReturn(
                        m::mock()->shouldReceive('setOption')->with('hint-below', 'current-authorisation-hint-10')->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('get')
            ->with('totAuthHgvVehiclesFieldset')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('totAuthHgvVehicles')
                    ->andReturn(
                        m::mock()->shouldReceive('setOption')->with('hint-below', 'current-authorisation-hint-11')->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('get')
            ->with('totAuthTrailersFieldset')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('totAuthTrailers')
                    ->andReturn(
                        m::mock()->shouldReceive('setOption')->with('hint-below', 'current-authorisation-hint-12')->getMock()
                    )
                    ->getMock()
            );

        $this->form->shouldReceive('get')
            ->with('data')
            ->andReturn($data);

        $this->mockFormHelper->shouldReceive('disableElement')
            ->with($this->form, 'data->totCommunityLicencesFieldset->totCommunityLicences');

        $form = $this->sut->getForm($params);
        $this->assertSame($this->form, $form);
    }

    protected function mockPopulateFormTable(array $data): Table
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
            ->with('lva-variation-operating-centres', $data, [])
            ->andReturn($table);

        $this->mockFormHelper->shouldReceive('populateFormTable')
            ->with($fieldset, $table);

        return $tableElement;
    }
}
