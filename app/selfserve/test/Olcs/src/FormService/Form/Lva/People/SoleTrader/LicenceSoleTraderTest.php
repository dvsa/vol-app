<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\People\SoleTrader;

use Common\Form\Element\DynamicMultiCheckbox;
use Common\Form\Element\DynamicRadio;
use Common\Form\Element\DynamicSelect;
use Common\FormService\FormServiceManager;
use Common\Service\Lva\PeopleLvaService;
use Common\Service\Translator\TranslationLoader;
use Laminas\Form\ElementInterface;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\People\SoleTrader\LicenceSoleTrader as Sut;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

final class LicenceSoleTraderTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    protected $mockLicenceService;
    private $peopleLvaService;

    #[\Override]
    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);

        $this->mockLicenceService = m::mock(Form::class);
        $this->peopleLvaService = m::mock(PeopleLvaService::class);

        /** @var FormServiceManager fsm */
        $fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $fsm->setService('lva-licence', $this->mockLicenceService);

        $this->sut = new Sut($this->formHelper, m::mock(AuthorizationService::class), $this->peopleLvaService, $fsm);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('noDisqualifyProvider')]
    public function testGetFormNoDisqualify(array $params): void
    {
        $params['canModify'] = true;

        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $formActions->shouldReceive('has')->with('disqualify')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('disqualify');

        $form = m::mock(Form::class);

        $this->mockLicenceService->shouldReceive('alterForm')
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
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $formActions->shouldReceive('get->setValue')
            ->once()
            ->with('foo');

        $form = m::mock(Form::class);

        $this->mockLicenceService->shouldReceive('alterForm')
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
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $formActions->shouldReceive('get->setValue')
            ->once()
            ->with('foo');

        $form = m::mock(Form::class);

        $this->mockLicenceService->shouldReceive('alterForm')
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
