<?php

namespace OlcsTest\FormService\Form\Lva\OperatingCentres;

use Olcs\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentres;
use Common\FormService\FormServiceInterface;
use Common\FormService\FormServiceManager;
use Common\Service\Table\TableBuilder;
use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Http\Request;
use Common\Service\Helper\FormHelperService;

/**
 * Licence Operating Centres Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceOperatingCentresTest extends MockeryTestCase
{
    protected $form;

    /**
     * @var LicenceOperatingCentres
     */
    protected $sut;

    protected $mockFormHelper;

    protected $tableBuilder;

    public function setUp()
    {
        $this->tableBuilder = m::mock();

        $sm = Bootstrap::getServiceManager();
        $sm->setService('Table', $this->tableBuilder);

        $fsm = m::mock(FormServiceManager::class)->makePartial();
        $fsm->shouldReceive('getServiceLocator')
            ->andReturn($sm);

        $this->form = m::mock(Form::class);

        $lvaLicence = m::mock(FormServiceInterface::class);
        $lvaLicence->shouldReceive('alterForm')
            ->once()
            ->with($this->form);

        $fsm->setService('lva-licence', $lvaLicence);

        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\OperatingCentres')
            ->andReturn($this->form);

        $this->sut = new LicenceOperatingCentres();
        $this->sut->setFormHelper($this->mockFormHelper);
        $this->sut->setFormServiceLocator($fsm);
    }

    public function testGetForm()
    {
        $params = [
            'operatingCentres' => [],
            'canHaveSchedule41' => true,
            'canHaveCommunityLicences' => true,
            'isPsv' => false,
        ];

        $this->mockPopulateFormTable([]);

        $this->mockFormHelper->shouldReceive('getValidator->setMessage')
            ->with('OperatingCentreNoOfOperatingCentres.required', 'required');

        $this->mockFormHelper->shouldReceive('remove')
            ->once()
            ->with($this->form, 'dataTrafficArea');

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
            ->with('totAuthVehicles')
            ->andReturn(false)
            ->shouldReceive('has')
            ->with('totAuthTrailers')
            ->andReturn(false)
            ->shouldReceive('has')
            ->with('totCommunityLicences')
            ->andReturn(false);

        $this->form->shouldReceive('get')
            ->with('data')
            ->andReturn($data);

        $this->mockFormHelper->shouldReceive('disableElements')
            ->with($data);

        $this->form->shouldReceive('has')
            ->with('dataTrafficArea')
            ->andReturn(true);

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $this->form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $this->form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->form->shouldReceive('get')
            ->with('dataTrafficArea')
            ->andReturn(
                m::mock()
                    ->shouldReceive('remove')
                    ->with('enforcementArea')
                    ->getMock()
            );

        $this->mockFormHelper->shouldReceive('lockElement')
            ->with($smallVehicles, 'operating-centres-licence-locked');

        $form = $this->sut->getForm($params);
        $this->assertSame($this->form, $form);
    }

    protected function mockPopulateFormTable($data)
    {
        $table = m::mock(TableBuilder::class);
        $tableElement = m::mock(Fieldset::class);

        $this->form->shouldReceive('get')
            ->with('table')
            ->andReturn($tableElement);

        $this->tableBuilder->shouldReceive('prepareTable')
            ->with('lva-licence-operating-centres', $data, [])
            ->andReturn($table);

        $this->mockFormHelper->shouldReceive('populateFormTable')
            ->with($tableElement, $table);

        return $tableElement;
    }
}
