<?php

declare(strict_types=1);

namespace CommonTest\FormService\Form\Lva\People\SoleTrader;

use Common\Form\Form;
use Common\FormService\Form\Lva\Application;
use Common\FormService\FormServiceManager;
use Common\Service\Lva\PeopleLvaService;
use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\People\SoleTrader\ApplicationSoleTrader as Sut;
use LmcRbacMvc\Service\AuthorizationService;

class ApplicationSoleTraderTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $authService;
    public $peopleLvaService;
    public $fsl;
    public $mockApplicationService;
    protected $sut;

    protected $formHelper;

    protected $fsm;

    protected $sm;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->authService = m::mock(AuthorizationService::class);
        $this->peopleLvaService = m::mock(PeopleLvaService::class);
        $this->fsl = m::mock(FormServiceManager::class)->makePartial();

        $this->mockApplicationService = m::mock(Application::class);

        $this->fsl->shouldReceive('get')
            ->with('lva-application')
            ->andReturn($this->mockApplicationService);

        $this->sut = new Sut($this->formHelper, $this->authService, $this->peopleLvaService);
    }

    /**
     * @dataProvider noDisqualifyProvider
     */
    public function testGetFormNoDisqualify($params): void
    {
        $params['canModify'] = true;

        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('has')->with('disqualify')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('disqualify');

        $form = m::mock(Form::class);

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\SoleTrader')
            ->andReturn($form);

        $this->sut->getForm($params);
    }

    public function testGetForm(): void
    {
        $params = [
            'location' => 'internal',
            'personId' => 123,
            'isDisqualified' => false,
            'canModify' => true,
            'disqualifyUrl' => 'foo'
        ];

        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('get->setValue')
            ->once()
            ->with('foo');

        $form = m::mock(Form::class);

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\SoleTrader')
            ->andReturn($form);

        $this->sut->getForm($params);
    }

    public function testGetFormCantModify(): void
    {
        $params = [
            'location' => 'internal',
            'personId' => 123,
            'isDisqualified' => false,
            'canModify' => false,
            'disqualifyUrl' => 'foo',
            'orgType' => 'bar'
        ];

        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('get->setValue')
            ->once()
            ->with('foo');

        $form = m::mock(Form::class);

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\SoleTrader')
            ->andReturn($form);

        $this->peopleLvaService->shouldReceive('lockPersonForm')
            ->once()
            ->with($form, 'bar');

        $this->sut->getForm($params);
    }

    /**
     * @return (int|null|string|true)[][][]
     *
     * @psalm-return list{list{array{location: 'external'}}, list{array{location: 'internal', personId: null}}, list{array{location: 'internal', personId: 123, isDisqualified: true}}}
     */
    public function noDisqualifyProvider(): array
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
