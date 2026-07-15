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

final class ApplicationSoleTraderTest extends MockeryTestCase
{
    public $peopleLvaService;
    protected $sut;

    protected $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $authService = m::mock(AuthorizationService::class);
        $this->peopleLvaService = m::mock(PeopleLvaService::class);
        $fsl = m::mock(FormServiceManager::class)->makePartial();

        $mockApplicationService = m::mock(Application::class);

        $fsl->shouldReceive('get')
            ->with('lva-application')
            ->andReturn($mockApplicationService);

        $this->sut = new Sut($this->formHelper, $authService, $this->peopleLvaService);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('noDisqualifyProvider')]
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
     * @return \Iterator<(int | string), array<array<(int | string | true | null)>>>
     *
     * @psalm-return list{list{array{location: 'external'}}, list{array{location: 'internal', personId: null}}, list{array{location: 'internal', personId: 123, isDisqualified: true}}}
     */
    public static function noDisqualifyProvider(): \Iterator
    {
        yield [
            ['location' => 'external']
        ];
        yield [
            [
                'location' => 'internal',
                'personId' => null
            ]
        ];
        yield [
            [
                'location' => 'internal',
                'personId' => 123,
                'isDisqualified' => true
            ]
        ];
    }
}
