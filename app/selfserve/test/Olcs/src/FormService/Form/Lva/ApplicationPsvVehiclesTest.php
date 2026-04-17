<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationPsvVehicles;
use Laminas\Form\Form;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application Psv Vehicles Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationPsvVehiclesTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationPsvVehicles
     */
    protected $sut;

    protected $fh;

    public function setUp(): void
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sut = new ApplicationPsvVehicles($this->fh, m::mock(\LmcRbacMvc\Service\AuthorizationService::class));
    }

    public function testAlterForm(): void
    {
        $mockForm = m::mock(Form::class);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\PsvVehicles')
            ->andReturn($mockForm)
            ->getMock();

        $this->mockAlterButtons($mockForm, $this->fh);

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
