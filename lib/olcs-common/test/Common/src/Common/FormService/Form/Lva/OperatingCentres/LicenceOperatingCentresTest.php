<?php

declare(strict_types=1);

namespace CommonTest\Common\FormService\Form\Lva\OperatingCentres;

use Common\Form\Elements\Types\Table;
use Common\Form\Elements\Validators\TableRequiredValidator;
use Common\Form\Form;
use Common\FormService\Form\Lva\Licence;
use Common\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentres;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Laminas\Form\ElementInterface;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\ValidatorChain;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Common\Service\Helper\FormHelperService;
use Common\RefData;
use LmcRbacMvc\Service\AuthorizationService;

final class LicenceOperatingCentresTest extends MockeryTestCase
{
    protected $form;

    /**
     * @var LicenceOperatingCentres
     */
    protected $sut;

    protected $mockFormHelper;

    protected $tableBuilder;

    protected $translator;

    #[\Override]
    protected function setUp(): void
    {
        $this->tableBuilder = m::mock(TableFactory::class);
        $authService = m::mock(AuthorizationService::class);
        $this->translator = m::mock(TranslationHelperService::class);

        $fsm = m::mock(FormServiceManager::class)->makePartial();

        $validators = [
            0 => [
                'instance' => m::mock(TableRequiredValidator::class),
            ],
        ];

        $validatorChain = m::mock(ValidatorChain::class);
        $validatorChain->expects('getValidators')->andReturn($validators);
        $rowsInput = m::mock(Input::class);
        $rowsInput->expects('getValidatorChain')->withNoArgs()->andReturn($validatorChain);

        $tableElement = m::mock(Table::class);
        $tableElement->expects('get')->with('rows')->andReturn($rowsInput);

        $inputFilter = m::mock(InputFilter::class);
        $inputFilter->expects('get')->with('table')->andReturn($tableElement);

        $this->form = m::mock(Form::class);
        $this->form->expects('getInputFilter')->withNoArgs()->andReturn($inputFilter);

        $lvaLicence = m::mock(Licence::class);
        $lvaLicence->expects('alterForm')
            ->with($this->form);

        $fsm->setService('lva-licence', $lvaLicence);

        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\OperatingCentres')
            ->andReturn($this->form);

        $this->sut = new LicenceOperatingCentres($this->mockFormHelper, $authService, $this->tableBuilder, $fsm);
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

        $data = m::mock(Form::class);
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
                m::mock(Fieldset::class)
                    ->shouldReceive('get')
                    ->with('totAuthLgvVehicles')
                    ->andReturn(
                        m::mock(ElementInterface::class)->shouldReceive('setOption')->with('hint-below', 'current-authorisation-hint-10')->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('get')
            ->with('totAuthHgvVehiclesFieldset')
            ->andReturn(
                m::mock(Fieldset::class)
                    ->shouldReceive('get')
                    ->with('totAuthHgvVehicles')
                    ->andReturn(
                        m::mock(ElementInterface::class)->shouldReceive('setOption')->with('hint-below', 'current-authorisation-hint-11')->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('get')
            ->with('totAuthTrailersFieldset')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('totAuthTrailers')
                    ->andReturn(
                        m::mock(ElementInterface::class)->shouldReceive('setOption')->with('hint-below', 'current-authorisation-hint-12')->getMock()
                    )
                    ->getMock()
            );

        $lockedElement = m::mock(Element::class);
        $lockedElement->shouldReceive('setOption')->with('hint-below', 'current-authorisation-hint-0')->getMock();

        $data->shouldReceive('get')
            ->with('totCommunityLicencesFieldset')
            ->andReturn(
                m::mock(Fieldset::class)
                    ->shouldReceive('get')
                    ->with('totCommunityLicences')
                    ->andReturn(
                        $lockedElement
                    )
                    ->getMock()
            );

        $this->mockFormHelper->shouldReceive('lockElement')
            ->with($lockedElement, 'community-licence-changes-contact-office')
            ->once();

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
            ->with('lva-operating-centres', $data, [])
            ->andReturn($table);

        $this->mockFormHelper->shouldReceive('populateFormTable')
            ->with($fieldset, $table);

        return $tableElement;
    }
}
