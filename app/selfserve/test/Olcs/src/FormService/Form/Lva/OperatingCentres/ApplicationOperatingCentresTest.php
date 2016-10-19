<?php

namespace OlcsTest\FormService\Form\Lva\OperatingCentres;

use Olcs\FormService\Form\Lva\OperatingCentres\ApplicationOperatingCentres;
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
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

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

    public function setUp()
    {
        $this->tableBuilder = m::mock();

        $sm = Bootstrap::getServiceManager();
        $sm->setService('Table', $this->tableBuilder);

        $fsm = m::mock(FormServiceManager::class)->makePartial();
        $fsm->shouldReceive('getServiceLocator')
            ->andReturn($sm);

        $this->form = m::mock(Form::class);

        $lvaApplication = m::mock(FormServiceInterface::class);
        $lvaApplication->shouldReceive('alterForm')
            ->once()
            ->with($this->form);

        $fsm->setService('lva-application', $lvaApplication);

        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\OperatingCentres')
            ->andReturn($this->form);

        $this->sut = new ApplicationOperatingCentres();
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
            ->with('totCommunityLicences')
            ->andReturn(true)
            ->shouldReceive('get')
            ->with('totCommunityLicences')
            ->andReturn($totCommunityLicences);

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
        $table = m::mock(TableBuilder::class);
        $tableElement = m::mock(Fieldset::class);

        $this->form->shouldReceive('get')
            ->with('table')
            ->andReturn($tableElement);

        $this->tableBuilder->shouldReceive('prepareTable')
            ->with('lva-operating-centres', $data, [])
            ->andReturn($table);

        $this->mockFormHelper->shouldReceive('populateFormTable')
            ->with($tableElement, $table);

        return $tableElement;
    }
}
