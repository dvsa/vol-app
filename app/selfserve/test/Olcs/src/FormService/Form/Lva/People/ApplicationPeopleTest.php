<?php

namespace OlcsTest\FormService\Form\Lva\People;

use Laminas\Form\ElementInterface;
use Mockery as m;
use Common\Form\Form;
use Common\FormService\FormServiceManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Helper\FormHelperService;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use Olcs\FormService\Form\Lva\People\ApplicationPeople as Sut;
use Common\Form\Elements\Validators\TableRequiredValidator;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application People Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationPeopleTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var Sut
     */
    protected $sut;

    /**
     * @var FormHelperService|m\Mock
     */
    protected $formHelper;

    /**
     * @var FormServiceManager|m\Mock
     */
    protected $fsm;

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $this->sut = new Sut($this->formHelper, m::mock(AuthorizationService::class));
    }

    public function testGetFormAndCanModify(): void
    {
        $formActions = m::mock(Form::class);
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');

        $form = m::mock(Form::class);
        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\People')
            ->andReturn($form);

        $this->mockAlterButtons($form, $this->formHelper, $formActions);

        $this->sut->getForm(['canModify' => true, 'isPartnership' => false]);
    }

    public function testGetFormAndCannotModify(): void
    {
        $formActions = m::mock(Form::class);
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);

        $formActions->shouldReceive('remove')->once()->with('cancel');

        $formActions->shouldReceive('get')
            ->with('save')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('setLabel')
                    ->with('lva.external.return.link')
                    ->once()
                    ->shouldReceive('removeAttribute')
                    ->with('class')
                    ->once()
                    ->shouldReceive('setAttribute')
                    ->with('class', 'govuk-button govuk-button--secondary')
                    ->once()
                    ->getMock()
            )
            ->times(3)
            ->getMock();

        $form = m::mock(Form::class);
        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\People')
            ->andReturn($form);

        $this->sut->getForm(['canModify' => false, 'isPartnership' => false]);
    }

    public function testGetFormPartnership(): void
    {
        $formActions = m::mock(Form::class);
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);

        $formActions->shouldReceive('remove')->once()->with('cancel');

        $formActions->shouldReceive('get')
            ->with('save')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('setLabel')
                    ->with('lva.external.return.link')
                    ->once()
                    ->shouldReceive('removeAttribute')
                    ->with('class')
                    ->once()
                    ->shouldReceive('setAttribute')
                    ->with('class', 'govuk-button govuk-button--secondary')
                    ->once()
                    ->getMock()
            )
            ->times(3)
            ->getMock();

        $form = m::mock(Form::class);
        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\People')
            ->andReturn($form)
            ->once()
            ->shouldReceive('removeValidator')
            ->with($form, 'table->rows', TableRequiredValidator::class)
            ->once()
            ->shouldReceive('attachValidator')
            ->with($form, 'table->rows', m::type(TableRequiredValidator::class))
            ->once()
            ->getMock();

        $this->sut->getForm(['canModify' => false, 'isPartnership' => true]);
    }
}
