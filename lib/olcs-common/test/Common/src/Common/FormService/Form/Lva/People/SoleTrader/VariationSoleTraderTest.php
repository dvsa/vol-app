<?php

declare(strict_types=1);

namespace CommonTest\FormService\Form\Lva\People\SoleTrader;

use Common\FormService\FormServiceManager;
use Common\Service\Lva\PeopleLvaService;
use Common\Service\Lva\VariationLvaService;
use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\People\SoleTrader\VariationSoleTrader as Sut;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

final class VariationSoleTraderTest extends MockeryTestCase
{
    public $peopleLvaService;
    protected $sut;

    protected $formHelper;

    protected $mockVariationService;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $authService = m::mock(AuthorizationService::class);
        $this->peopleLvaService = m::mock(PeopleLvaService::class);
        $this->mockVariationService = m::mock(VariationLvaService::class);
        $fsl = m::mock(FormServiceManager::class)->makePartial();

        $fsl->shouldReceive('get')
            ->with('lva-variation')
            ->andReturn($this->mockVariationService);

        $this->sut = new Sut($this->formHelper, $authService, $this->peopleLvaService, $fsl);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('noDisqualifyProvider')]
    public function testGetFormNoDisqualify($params): void
    {
        $params['canModify'] = true;

        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('has')->with('disqualify')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('disqualify');

        $form = m::mock(\Common\Form\Form::class);

        $this->mockVariationService->shouldReceive('alterForm')
            ->once()
            ->with($form);

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

        $form = m::mock();

        $this->mockVariationService->shouldReceive('alterForm')
            ->once()
            ->with($form);

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

        $this->mockVariationService->shouldReceive('alterForm')
            ->once()
            ->with($form);

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
