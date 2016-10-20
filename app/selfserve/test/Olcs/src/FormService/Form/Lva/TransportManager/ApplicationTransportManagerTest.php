<?php

namespace OlcsTest\FormService\Form\Lva\TransportManager;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\TransportManager\ApplicationTransportManager as Sut;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\Service\Helper\FormHelperService;
use Common\FormService\FormServiceManager;

/**
 * Application TransportManager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationTransportManagerTest extends MockeryTestCase
{
    use ButtonsAlterations;

    protected $sut;

    protected $formHelper;

    protected $fsm;

    public function setUp()
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();

        $this->sut = new Sut();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    public function testGetForm()
    {
        $form = m::mock();

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\TransportManagers')
            ->andReturn($form);

        $this->mockAlterButtons($form, $this->formHelper);

        $this->sut->getForm();
    }
}
