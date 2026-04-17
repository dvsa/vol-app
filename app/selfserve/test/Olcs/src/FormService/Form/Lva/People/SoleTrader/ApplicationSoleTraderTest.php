<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\People\SoleTrader;

use Common\Form\Element\DynamicMultiCheckbox;
use Common\Form\Element\DynamicRadio;
use Common\Form\Element\DynamicSelect;
use Common\Form\Form;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Lva\PeopleLvaService;
use Common\Service\Translator\TranslationLoader;
use Laminas\Form\ElementInterface;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Olcs\FormService\Form\Lva\People\SoleTrader\ApplicationSoleTrader;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\People\SoleTrader\ApplicationSoleTrader as Sut;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LmcRbacMvc\Service\AuthorizationService;

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

    /**
     * @var PeopleLvaService|(PeopleLvaService&m\LegacyMockInterface)|(PeopleLvaService&m\MockInterface)|m\LegacyMockInterface|m\MockInterface
     */
    private $peopleLvaService;

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);

        /** @var FormServiceManager fsm */
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $this->peopleLvaService = m::mock(PeopleLvaService::class);

        $this->sut = new Sut($this->formHelper, m::mock(AuthorizationService::class), $this->peopleLvaService);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('noDisqualifyProvider')]
    public function testGetFormNoDisqualify(array $params): void
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

        $this->mockAlterButtons($form, $this->formHelper, $formActions);

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

        $formActions = m::mock(ElementInterface::class)
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

        $formActions = m::mock(ElementInterface::class)
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
                    ->with('class', 'govuk-button govuk-button--secondary')
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
    public static function noDisqualifyProvider(): array
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
