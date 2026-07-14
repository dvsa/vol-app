<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\TransportManager;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\TransportManager\ApplicationTransportManager as Sut;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\Service\Helper\FormHelperService;
use Common\FormService\FormServiceManager;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application TransportManager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class ApplicationTransportManagerTest extends MockeryTestCase
{
    use ButtonsAlterations;

    protected $sut;

    protected $formHelper;

    #[\Override]
    public function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $fsm = m::mock(FormServiceManager::class)->makePartial();

        $this->sut = new Sut($this->formHelper, m::mock(AuthorizationService::class));
    }

    public function testGetForm(): void
    {
        $form = m::mock();

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\TransportManagers')
            ->andReturn($form);

        $this->mockAlterButtons($form, $this->formHelper);

        $this->sut->getForm();
    }
}
