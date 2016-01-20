<?php

/**
 * Application Business Type Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva\BusinessType;

use Common\Form\Elements\InputFilters\Lva\BackToApplicationActionLink;
use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\BusinessType\ApplicationBusinessType;
use Common\FormService\FormServiceInterface;
use Zend\Form\Form;
use Zend\Form\Element;

/**
 * Application Business Type Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessTypeTest extends MockeryTestCase
{
    /**
     * @var ApplicationBusinessType
     */
    protected $sut;

    protected $fsm;

    protected $fh;

    protected $sm;

    public function setUp()
    {
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $this->sut = new ApplicationBusinessType();
        $this->sut->setFormServiceLocator($this->fsm);
        $this->sut->setFormHelper($this->fh);
        $this->fsm->setServiceLocator($this->sm); // main service locator is accessed via form service manager
    }

    public function testGetFormWithoutInforceLicences()
    {
        $mockForm = m::mock(Form::class);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\BusinessType')
            ->andReturn($mockForm);

        $mockApplication = m::mock(FormServiceInterface::class);
        $mockApplication->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $this->fsm->setService('lva-application', $mockApplication);

        $form = $this->sut->getForm(false);

        $this->assertSame($mockForm, $form);
    }

    public function testGetFormWithInforceLicences()
    {
        $mockElement = m::mock(Element::class);

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);

        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('remove')->once()->with('cancel');

        $formActions->shouldReceive('add')->once()->with(m::type(BackToApplicationActionLink::class));

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')->with('data')
            ->andReturn(
                m::mock()->shouldReceive('get')
                    ->with('type')
                    ->andReturn($mockElement)
                    ->getMock()
            );

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\BusinessType')
            ->andReturn($mockForm)
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockElement, 'business-type.locked')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'data->type');

        $mockApplication = m::mock(FormServiceInterface::class);
        $mockApplication->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $this->fsm->setService('lva-application', $mockApplication);

        $this->sm
            ->shouldReceive('get')
            ->with('Helper\Guidance')
            ->andReturn(
                m::mock()
                    ->shouldReceive('append')
                    ->with('business-type.locked.message')
                    ->once()
                    ->getMock()
            );

        $form = $this->sut->getForm(true);

        $this->assertSame($mockForm, $form);
    }
}
