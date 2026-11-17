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
use Olcs\FormService\Form\Lva\FinancialEvidence;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\FormService\Form\Lva\FinancialEvidence::class)]
final class FinancialEvidenceTest extends MockeryTestCase
{
    /** @var  FinancialEvidence */
    protected $sut;

    /** @var  m\MockInterface|\Common\Service\Helper\FormHelperService */
    protected $formHelper;
    /** @var  m\MockInterface */
    protected $urlHelper;
    /** @var  m\MockInterface */
    protected $translator;

    #[\Override]
    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->translator = m::mock(TranslationHelperService::class);
        $validatorPluginManager = m::mock(ValidatorPluginManager::class);
        $validatorPluginManager->shouldReceive('get')
            ->with(ValidateIf::class)
            ->andReturn($this->createMock(ValidateIf::class), $this->createMock(ValidateIf::class))
            ->twice();

        $serviceManager = $this->createMock(ServiceLocatorInterface::class);
        $serviceManager->method('get')->willReturnMap([
            ['Helper\Url', $this->urlHelper],
            ['Helper\Translation', $this->translator],
        ]);

        $this->sut = new FinancialEvidence(
            $this->formHelper,
            m::mock(\LmcRbacMvc\Service\AuthorizationService::class),
            $this->translator,
            $this->urlHelper,
            $validatorPluginManager
        );
    }

    public function testGetForm(): void
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
        $validatorChain->shouldReceive('attach')->twice();
        $validatorChain->shouldReceive('getValidators')->andReturn([]);

        $inputFilter = m::mock(InputFilter::class);
        $evidenceInputFilter = m::mock(InputFilter::class);
        $inputFilter->shouldReceive('get')->with('evidence')->andReturn($evidenceInputFilter);
        $uploadNowInput = m::mock(\Laminas\InputFilter\Input::class);
        $uploadNowInput->shouldReceive('setRequired')->with(false);
        $uploadNowInput->shouldReceive('setContinueIfEmpty')->with(true);
        $uploadNowInput->shouldReceive('getValidatorChain')->andReturn($validatorChain);
        $uploadLaterInput = m::mock(\Laminas\InputFilter\Input::class);
        $uploadLaterInput->shouldReceive('setRequired')->with(false);
        $uploadedFileCountInput = m::mock(\Laminas\InputFilter\Input::class);
        $uploadedFileCountInput->shouldReceive('getValidatorChain')->andReturn($validatorChain);
        $evidenceInputFilter->shouldReceive('get')->with('uploadNowRadio')->andReturn($uploadNowInput);
        $evidenceInputFilter->shouldReceive('get')->with('uploadLaterRadio')->andReturn($uploadLaterInput);
        $evidenceInputFilter->shouldReceive('get')->with('uploadedFileCount')->andReturn($uploadedFileCountInput);

        $mockForm->shouldReceive('getInputFilter')->andReturn($inputFilter);

        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('get->setLabel')->once();

        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);
        $mockForm->shouldReceive('get')
            ->with('evidence')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('uploadNowRadio')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                        ->shouldReceive('setName')
                        ->with('uploadNow')
                        ->once()
                        ->shouldReceive('setAttribute')
                        ->with('required', true)
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
