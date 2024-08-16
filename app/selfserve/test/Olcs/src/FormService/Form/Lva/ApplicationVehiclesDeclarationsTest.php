<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationVehiclesDeclarations;
use Laminas\Form\Form;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application Vehicles Declarations Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationVehiclesDeclarationsTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationVehiclesDeclarations
     */
    protected $sut;

    protected $fh;

    public function setUp(): void
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sut = new ApplicationVehiclesDeclarations($this->fh);
    }

    public function testAlterForm(): void
    {
        $mockForm = m::mock(Form::class);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\VehiclesDeclarations')
            ->andReturn($mockForm)
            ->getMock();

        $this->mockAlterButtons($mockForm, $this->fh);

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
