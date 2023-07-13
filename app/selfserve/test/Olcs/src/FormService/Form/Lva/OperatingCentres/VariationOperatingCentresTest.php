<?php

namespace OlcsTest\FormService\Form\Lva\OperatingCentres;

use Common\Service\Helper\TranslationHelperService;
use Olcs\FormService\Form\Lva\OperatingCentres\VariationOperatingCentres;
use Common\FormService\FormServiceManager;
use Common\Service\Table\TableBuilder;
use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Common\Service\Helper\FormHelperService;
use Common\RefData;
use ZfcRbac\Service\AuthorizationService;

class VariationOperatingCentresTest extends MockeryTestCase
{
    protected $form;

    /**
     * @var VariationOperatingCentres
     */
    protected $sut;

    protected $mockFormHelper;

    protected $tableBuilder;

    protected $translator;

    public function setUp(): void
    {
        $this->tableBuilder = m::mock();

        $this->translator = m::mock(TranslationHelperService::class);

        $sm = Bootstrap::getServiceManager();
        $sm->setService('Table', $this->tableBuilder);
        $sm->setService('Helper\Translation', $this->translator);

        $fsm = m::mock(FormServiceManager::class)->makePartial();
        $fsm->shouldReceive('getServiceLocator')
            ->andReturn($sm);

        $this->form = m::mock(Form::class);

        $lvaVariation = m::mock(Form::class);
        $lvaVariation->shouldReceive('alterForm')
            ->once()
            ->with($this->form);

        $fsm->setService('lva-variation', $lvaVariation);

        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\OperatingCentres')
            ->andReturn($this->form);

        $this->sut = new VariationOperatingCentres($this->mockFormHelper, m::mock(AuthorizationService::class), $this->tableBuilder, $fsm, $this->translator);
    }

    public function testGetForm()
    {
        $params = [
            'operatingCentres' => [],
            'canHaveSchedule41' => true,
            'canHaveCommunityLicences' => true,
            'isPsv' => false,
            'licence' => [
                'totAuthHgvVehicles' => 10,
                'totAuthLgvVehicles' => 11,
                'totAuthTrailers' => 12
            ],
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

        $this->mockFormHelper->shouldReceive('remove')
            ->once()
            ->with($this->form, 'form-actions->cancel');

        $this->translator->shouldReceive('translateReplace')
            ->with('current-authorisation-hint', [10])
            ->andReturn('current-authorisation-hint-10')
            ->shouldReceive('translateReplace')
            ->with('current-authorisation-hint', [11])
            ->andReturn('current-authorisation-hint-11')
            ->shouldReceive('translateReplace')
            ->with('current-authorisation-hint', [12])
            ->andReturn('current-authorisation-hint-12');

        $totCommunityLicences = m::mock(Element::class);

        $data = m::mock();
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
            ->with('totAuthHgvVehiclesFieldset')
            ->andReturn(
                m::mock()->shouldReceive('get')
                    ->with('totAuthHgvVehicles')
                    ->andReturn(
                        m::mock()->shouldReceive('setOption')
                        ->with('hint-below', 'current-authorisation-hint-10')
                        ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('get')
            ->with('totAuthLgvVehiclesFieldset')
            ->andReturn(
                m::mock()->shouldReceive('get')
                    ->with('totAuthLgvVehicles')
                    ->andReturn(
                        m::mock()->shouldReceive('setOption')
                            ->with('hint-below', 'current-authorisation-hint-11')
                            ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('get')
            ->with('totAuthTrailersFieldset')
            ->andReturn(
                m::mock()->shouldReceive('get')
                    ->with('totAuthTrailers')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setOption')
                            ->with('hint-below', 'current-authorisation-hint-12')
                            ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('get')
            ->with('totCommunityLicencesFieldset')
            ->andReturn(
                m::mock()->shouldReceive('get')
                    ->with('totCommunityLicences')
                    ->andReturn($totCommunityLicences)
                    ->getMock()
            );

        $this->form->shouldReceive('get')
            ->with('data')
            ->andReturn($data);

        $this->mockFormHelper->shouldReceive('disableElement')
            ->with($this->form, 'data->totCommunityLicencesFieldset->totCommunityLicences');

        $columns = [
            'noOfVehiclesRequired' => [
                'title' => 'unmodified-column-name'
            ]
        ];

        $expectedModifiedColumns = [
            'noOfVehiclesRequired' => [
                'title' => 'application_operating-centres_authorisation.table.hgvs'
            ]
        ];

        $tableBuilder = m::mock(TableBuilder::class);
        $tableBuilder->shouldReceive('removeColumn')
            ->with('noOfComplaints')
            ->once();
        $tableBuilder->shouldReceive('getColumns')
            ->withNoArgs()
            ->andReturn($columns);
        $tableBuilder->shouldReceive('setColumns')
            ->with($expectedModifiedColumns)
            ->once();

        $tableElement->shouldReceive('get->getTable')
            ->withNoArgs()
            ->andReturn($tableBuilder);

        $this->mockFormHelper->shouldReceive('lockElement')
            ->with($totCommunityLicences, 'community-licence-changes-contact-office');

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

        $form = $this->sut->getForm($params);
        $this->assertSame($this->form, $form);
    }

    protected function mockPopulateFormTable($data)
    {
        $table = m::mock(TableBuilder::class);
        $tableElement = m::mock(Fieldset::class);

        $this->form->shouldReceive('has')
            ->with('table')
            ->andReturnTrue();

        $this->form->shouldReceive('get')
            ->with('table')
            ->andReturn($tableElement);

        $this->tableBuilder->shouldReceive('prepareTable')
            ->with('lva-variation-operating-centres', $data, [])
            ->andReturn($table);

        $this->mockFormHelper->shouldReceive('populateFormTable')
            ->with($tableElement, $table);

        return $tableElement;
    }
}
