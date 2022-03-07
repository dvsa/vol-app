<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\OperatingCentres;

use Olcs\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentres;
use Common\FormService\FormServiceInterface;
use Common\FormService\FormServiceManager;
use Common\Service\Table\TableBuilder;
use OlcsTest\Bootstrap;
use Mockery as m;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Common\Service\Helper\FormHelperService;
use Common\RefData;
use \Common\FormService\Form\Lva\Licence as LvaLicenceFormService;
use Laminas\View\Renderer\PhpRenderer;
use Common\Test\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentresTestCase;
use Common\Form\Elements\Types\Table as TableElement;

/**
 * @see LicenceOperatingCentres
 */
class LicenceOperatingCentresTest extends LicenceOperatingCentresTestCase
{
    protected const LOCKED_ELEMENT_MESSAGE = 'operating-centres-licence-locked';

    /**
     * @var LicenceOperatingCentres
     */
    protected $sut;

    public function testGetForm()
    {
        $tableBuilder = m::mock();

        $sm = Bootstrap::getServiceManager();
        $sm->setService('Table', $tableBuilder);

        $fsm = m::mock(FormServiceManager::class)->makePartial();
        $fsm->shouldReceive('getServiceLocator')
            ->andReturn($sm);

        $form = m::mock(Form::class);

        $lvaLicence = m::mock(FormServiceInterface::class);
        $lvaLicence->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $fsm->setService('lva-licence', $lvaLicence);

        $mockFormHelper = m::mock(FormHelperService::class);
        $mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\OperatingCentres')
            ->andReturn($form);

        $sut = new LicenceOperatingCentres();
        $sut->setFormHelper($mockFormHelper);
        $sut->setFormServiceLocator($fsm);

        $params = [
            'operatingCentres' => [],
            'canHaveSchedule41' => true,
            'canHaveCommunityLicences' => true,
            'isPsv' => false,
            'licenceType' => ['id' => RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            'vehicleType' => ['id' => RefData::APP_VEHICLE_TYPE_MIXED],
            'totAuthLgvVehicles' => 0,
        ];

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

        $table = m::mock(TableBuilder::class);
        $table->shouldReceive('getColumns')
            ->withNoArgs()
            ->andReturn($columns);
        $table->shouldReceive('setColumns')
            ->with($expectedModifiedColumns)
            ->once();

        $tableElement = m::mock(TableElement::class);
        $tableElement->shouldReceive('getTable')
            ->withNoArgs()
            ->andReturn($table);

        $tableFieldset = m::mock(Fieldset::class);
        $tableFieldset->shouldReceive('get')
            ->with('table')
            ->andReturn($tableElement);

        $form->shouldReceive('has')
            ->with('table')
            ->andReturnTrue();

        $form->shouldReceive('get')
            ->with('table')
            ->andReturn($tableFieldset);

        $tableBuilder->shouldReceive('prepareTable')
            ->with('lva-licence-operating-centres', [], [])
            ->andReturn($table);

        $mockFormHelper->shouldReceive('populateFormTable')
            ->with($tableFieldset, $table);

        $mockFormHelper->shouldReceive('getValidator->setMessage')
            ->with('OperatingCentreNoOfOperatingCentres.required', 'required');

        $mockFormHelper->shouldReceive('remove')
            ->once()
            ->with($form, 'dataTrafficArea');

        $smallVehicles = m::mock(Element::class);

        $data = m::mock();
        $data->shouldReceive('has')
            ->with('totAuthSmallVehicles')
            ->andReturn(true)
            ->shouldReceive('get')
            ->with('totAuthSmallVehicles')
            ->andReturn($smallVehicles)
            ->shouldReceive('has')
            ->with('totAuthMediumVehicles')
            ->andReturn(false)
            ->shouldReceive('has')
            ->with('totAuthLargeVehicles')
            ->andReturn(false)
            ->shouldReceive('has')
            ->with('totAuthHgvVehiclesFieldset')
            ->andReturn(false)
            ->shouldReceive('has')
            ->with('totAuthLgvVehiclesFieldset')
            ->andReturn(false)
            ->shouldReceive('has')
            ->with('totAuthTrailersFieldset')
            ->andReturn(false)
            ->shouldReceive('has')
            ->with('totCommunityLicencesFieldset')
            ->andReturn(false);

        $form->shouldReceive('get')
            ->with('data')
            ->andReturn($data);

        $mockFormHelper->shouldReceive('disableElements')
            ->with($data);

        $form->shouldReceive('has')
            ->with('dataTrafficArea')
            ->andReturn(true);

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $form->shouldReceive('get')
            ->with('dataTrafficArea')
            ->andReturn(
                m::mock()
                    ->shouldReceive('remove')
                    ->with('enforcementArea')
                    ->getMock()
            );

        $mockFormHelper->shouldReceive('lockElement')
            ->with($smallVehicles, 'operating-centres-licence-locked');

        $this->assertSame($form, $sut->getForm($params));
    }

    /**
     * @test
     */
    public function getForm_IsCallable()
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getForm']);
    }

    /**
     * @test
     * @depends getForm_IsCallable
     */
    public function getForm_ReturnsAForm()
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getForm($this->paramsForLicence());

        // Assert
        $this->assertInstanceOf(Form::class, $result);
    }

    /**
     * @return array
     */
    public function lockedFieldNamesDataProvider(): array
    {
        return [
            'totAuthHgvVehicles' => ['totAuthHgvVehicles', $this->paramsForLicence()],
            'totAuthLgvVehicles' => ['totAuthLgvVehicles', $this->paramsForMixedLicenceWithLgv()],
            'totAuthTrailers' => ['totAuthTrailers', $this->paramsForLicence()],
        ];
    }

    /**
     * @param string $fieldName
     * @param array $params
     * @test
     * @depends getForm_IsCallable
     * @dataProvider lockedFieldNamesDataProvider
     */
    public function getForm_LocksFields(string $fieldName, array $params)
    {
        // Setup
        $this->overrideFormHelperWithMock();
        $this->setUpSut();

        // Expect
        $this->formHelper()->expects('lockElement')->withArgs(function ($element, $message) use ($fieldName) {
            if (($element instanceof Element || $element instanceof Fieldset) && $element->getName() === $fieldName) {
                $this->assertSame(static::LOCKED_ELEMENT_MESSAGE, $message);
                return true;
            }
            return false;
        });

        // Execute
        $this->sut->getForm($params);
    }

    protected function setUpDefaultServices()
    {
        parent::setUpDefaultServices();
        $this->viewRenderer();
        $this->lvaLicenceFormService();
    }

    protected function setUpSut()
    {
        $this->sut = new LicenceOperatingCentres();
        $this->sut->setFormHelper($this->formHelper());
        $this->sut->setFormServiceLocator($this->formServiceManager());
    }

    /**
     * @return PhpRenderer
     */
    protected function viewRenderer(): PhpRenderer
    {
        if (!$this->serviceManager()->has('ViewRenderer')) {
            $instance = $this->setUpMockService(PhpRenderer::class);
            $this->serviceManager()->setService('ViewRenderer', $instance);
        }
        return $this->serviceManager()->get('ViewRenderer');
    }

    /**
     * @return LvaLicenceFormService
     */
    protected function lvaLicenceFormService(): LvaLicenceFormService
    {
        if (!$this->formServiceManager()->has('lva-licence')) {
            $instance = new LvaLicenceFormService();
            $this->formServiceManager()->setService('lva-licence', $instance);
        }
        return $this->formServiceManager()->get('lva-licence');
    }
}
