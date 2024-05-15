<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\Form\Form;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Validator\ValidateIf;
use Laminas\Form\ElementInterface;
use Laminas\InputFilter\InputFilter;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\VariationFinancialEvidence;

/**
 * @covers Olcs\FormService\Form\Lva\VariationFinancialEvidence
 */
class VariationFinancialEvidenceTest extends MockeryTestCase
{
    /** @var  VariationFinancialEvidence */
    protected $sut;

    /** @var  m\MockInterface|\Common\Service\Helper\FormHelperService */
    protected $formHelper;
    /** @var  \Common\FormService\FormServiceManager */
    protected $fsm;
    /** @var  m\MockInterface */
    protected $urlHelper;
    /** @var  m\MockInterface */
    protected $translator;

    public $validatorPluginManager;

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->translator = m::mock(TranslationHelperService::class);
        $this->validatorPluginManager = m::mock(ValidatorPluginManager::class);
        $validateIf = $this->createMock(ValidateIf::class);
        $this->validatorPluginManager->shouldReceive('get')->with(ValidateIf::class)->andReturn($validateIf);

        $serviceManager = $this->createMock(ServiceLocatorInterface::class);
        $serviceManager->method('get')->willReturnMap([
            ['Helper\Url', $this->urlHelper],
            ['Helper\Translation', $this->translator],
        ]);

        $this->fsm->shouldReceive('getServiceLocator')->andReturn($serviceManager);

        $this->sut = new VariationFinancialEvidence(
            $this->formHelper,
            m::mock(\LmcRbacMvc\Service\AuthorizationService::class),
            $this->translator,
            $this->urlHelper,
            $this->validatorPluginManager
        );
    }

    public function testGetForm()
    {
        $this->translator
            ->shouldReceive('translateReplace')
            ->with('lva-financial-evidence-evidence.hint', ['FOO'])
            ->andReturn('BAR')
            ->once()
            ->getMock();

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with('guides/guide', ['guide' => 'financial-evidence'], [], true)
            ->andReturn('FOO')
            ->once()
            ->getMock();

        /** @var \Laminas\Http\Request $request */
        $request = m::mock(\Laminas\Http\Request::class);

        // Mocks
        $mockForm = m::mock(Form::class);

        $validatorChain = m::mock(ValidatorChain::class);
        $validatorChain->shouldReceive('attach');
        $validatorChain->shouldReceive('getValidators')->andReturn([]);

        $inputFilter = m::mock(InputFilter::class);
        $inputFilter->shouldReceive('get')->andReturnSelf();
        $inputFilter->shouldReceive('setRequired');
        $inputFilter->shouldReceive('getValidatorChain')->andReturn($validatorChain);

        $mockForm->shouldReceive('getInputFilter')->andReturn($inputFilter);

        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');
        $formActions->shouldReceive('get->setLabel')->once();

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);
        $mockForm->shouldReceive('get')
            ->with('evidence')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('uploadNowRadio')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('get')
                    ->with('uploadLaterRadio')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('get')
                    ->with('sendByPostRadio')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('setOption')
                    ->with('hint', 'BAR')
                    ->once()
                    ->getMock()
            )
            ->once();

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\FinancialEvidence', $request)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'evidence->uploadNow')
            ->once();

        $form = $this->sut->getForm($request);

        $this->assertSame($mockForm, $form);
    }
}
