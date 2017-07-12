<?php

namespace OlcsTest\FormService\Form\Lva\People\SoleTrader;

use Common\Form\Elements\InputFilters\Lva\BackToApplicationActionLink;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Olcs\FormService\Form\Lva\People\SoleTrader\ApplicationSoleTrader;
use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\People\SoleTrader\ApplicationSoleTrader as Sut;
use Zend\Form\Form;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Application Sole Trader Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationSoleTraderTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationSoleTrader
     */
    protected $sut;

    /**
     * @var FormHelperService|m
     */
    protected $formHelper;

    /**
     * @var FormServiceManager|m
     */
    protected $fsm;

    /**
     * @var ServiceLocatorInterface
     */
    protected $sm;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');

        $this->sm = Bootstrap::getServiceManager();

        /** @var FormServiceManager fsm */
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();
        $this->fsm->setServiceLocator($this->sm);

        $this->sut = new Sut();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    /**
     * @dataProvider noDisqualifyProvider
     */
    public function testGetFormNoDisqualify($params)
    {
        $params['canModify'] = true;

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('disqualify')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('disqualify');

        $form = m::mock();

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\SoleTrader')
            ->andReturn($form);

        $this->mockAlterButtons($form, $this->formHelper, $formActions);

        $this->sut->getForm($params);
    }

    public function testGetForm()
    {
        $params = [
            'location' => 'internal',
            'personId' => 123,
            'isDisqualified' => false,
            'canModify' => true,
            'disqualifyUrl' => 'foo'
        ];

        $formActions = m::mock()
            ->shouldReceive('get')
            ->with('disqualify')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('foo')
                    ->once()
                    ->getMock()
            )
            ->getMock();

        $form = m::mock();

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\SoleTrader')
            ->andReturn($form);

        $this->mockAlterButtons($form, $this->formHelper, $formActions);

        $this->sut->getForm($params);
    }

    public function testGetFormCantModify()
    {
        $params = [
            'location' => 'internal',
            'personId' => 123,
            'isDisqualified' => false,
            'canModify' => false,
            'disqualifyUrl' => 'foo',
            'orgType' => 'bar'
        ];

        $formActions = m::mock(\Common\Form\Form::class)
            ->shouldReceive('get')
            ->with('disqualify')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setValue')
                    ->with('foo')
                    ->once()
                    ->getMock()
            )
            ->getMock();

        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('get')
            ->with('save')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setLabel')
                    ->with('lva.external.return.link')
                    ->once()
                    ->shouldReceive('removeAttribute')
                    ->with('class')
                    ->once()
                    ->shouldReceive('setAttribute')
                    ->with('class', 'action--tertiary large')
                    ->once()
                    ->getMock()
            )
            ->times(3)
            ->getMock();

        $form = m::mock(\Common\Form\Form::class);

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\SoleTrader')
            ->andReturn($form);

        $peopleService = m::mock();
        $peopleService->shouldReceive('lockPersonForm')
            ->once()
            ->with($form, 'bar');

        $this->sm->setService('Lva\People', $peopleService);

        $this->sut->getForm($params);
    }

    public function noDisqualifyProvider()
    {
        return [
            [
                ['location' => 'external']
            ],
            [
                [
                    'location' => 'internal',
                    'personId' => null
                ]
            ],
            [
                [
                    'location' => 'internal',
                    'personId' => 123,
                    'isDisqualified' => true
                ]
            ],
        ];
    }
}
