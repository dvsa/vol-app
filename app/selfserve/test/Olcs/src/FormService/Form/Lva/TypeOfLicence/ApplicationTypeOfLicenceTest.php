<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\TypeOfLicence;

use Common\Rbac\Service\Permission;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\TypeOfLicence\ApplicationTypeOfLicence;
use Laminas\Form\Form;
use Common\FormService\FormServiceManager;

class ApplicationTypeOfLicenceTest extends MockeryTestCase
{
    /**
     * @var ApplicationTypeOfLicence
     */
    protected $sut;

    protected $fh;

    protected $fsm;
    private $permission;

    public function setUp(): void
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $guidanceHelper = m::mock(GuidanceHelperService::class);
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();
        $this->permission = m::mock(Permission::class);
        $this->sut = new ApplicationTypeOfLicence($this->fh, $this->permission, $guidanceHelper, $this->fsm);
    }

    public function testAlterForm(): void
    {
        $this->permission->expects('isInternalReadOnly')->withNoArgs()->andReturnFalse();

        $mockForm = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock(ElementInterface::class)
                ->shouldReceive('get')
                ->with('saveAndContinue')
                ->andReturn(
                    m::mock(ElementInterface::class)
                    ->shouldReceive('setLabel')
                    ->with('lva.external.save_and_continue.button')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\TypeOfLicence')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->save')
            ->once()
            ->getMock();

        $this->fsm->shouldReceive('get')
            ->with('lva-application')
            ->once()
            ->andReturn(
                m::mock(ElementInterface::class)
                ->shouldReceive('alterForm')
                ->with($mockForm)
                ->once()
                ->getMock()
            )
            ->getMock();

        $form = $this->sut->getForm([]);

        $this->assertSame($mockForm, $form);
    }
}
