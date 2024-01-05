<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Form\Element\DynamicMultiCheckbox;
use Common\Form\Element\DynamicRadio;
use Common\Form\Element\DynamicSelect;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Translator\TranslationLoader;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationFinancialEvidence;
use Laminas\Form\Form;
use Laminas\Http\Request;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use ZfcRbac\Service\AuthorizationService;

/**
 * Application Financial Evidence Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationFinancialEvidenceTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationFinancialEvidence
     */
    protected $sut;

    /** @var  \Common\FormService\FormServiceManager */
    protected $fsm;
    /** @var  var FormHelperService */
    protected $fh;
    /** @var  m\MockInterface */
    protected $urlHelper;
    /** @var  m\MockInterface */
    protected $translator;

    /**
     * We can access service manager if we need to add a mock for certain applications
     *
     * @return \Laminas\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceManager()
    {
            $serviceManager =  self::getRealServiceManager();
            $serviceManager->setAllowOverride(true);

            $serviceManager->get('FormElementManager')->setFactory(
                'DynamicSelect',
                function ($serviceLocator, $name, $requestedName) {
                    $element = new DynamicSelect();
                    $element->setValueOptions(
                        [
                            '1' => 'one',
                            '2' => 'two',
                            '3' => 'three'
                        ]
                    );
                    return $element;
                }
            );

            $serviceManager->get('FormElementManager')->setFactory(
                'DynamicRadio',
                function ($serviceLocator, $name, $requestedName) {
                    $element = new DynamicRadio();
                    $element->setValueOptions(
                        [
                            '1' => 'one',
                            '2' => 'two',
                            '3' => 'three'
                        ]
                    );
                    return $element;
                }
            );

            $serviceManager->get('FormElementManager')->setFactory(
                'Common\Form\Element\DynamicMultiCheckbox',
                function ($serviceLocator, $name, $requestedName) {
                    $element = new DynamicMultiCheckbox();
                    $element->setValueOptions(
                        [
                            '1' => 'one',
                            '2' => 'two',
                            '3' => 'three'
                        ]
                    );
                    return $element;
                }
            );

        return $serviceManager;
    }

    /**
     * Added this method for backwards compatibility
     *
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public static function getRealServiceManager()
    {
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $config = include 'config/application.config.php';
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        $serviceManager->setAllowOverride(true);

        $mockTranslationLoader = m::mock(TranslationLoader::class);
        $mockTranslationLoader->shouldReceive('load')->andReturn(['default' => ['en_GB' => []]]);
        $mockTranslationLoader->shouldReceive('loadReplacements')->andReturn([]);
        $serviceManager->setService(TranslationLoader::class, $mockTranslationLoader);

        $pluginManager = new LoaderPluginManager($serviceManager);
        $pluginManager->setService(TranslationLoader::class, $mockTranslationLoader);
        $serviceManager->setService('TranslatorPluginManager', $pluginManager);

        // Mess up the backend, so any real rest calls will fail
        $config = $serviceManager->get('Config');
        $config['service_api_mapping']['endpoints']['backend'] = 'http://some-fake-backend/';
        $serviceManager->setService('Config', $config);

        return $serviceManager;
    }

    public function setUp(): void
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->translator = m::mock(TranslationHelperService::class);

        $sm = $this->getServiceManager();
        $sm->setService('Helper\Url', $this->urlHelper);
        $sm->setService('Helper\Translation', $this->translator);

        $this->fsm->shouldReceive('getServiceLocator')->andReturn($sm);

        $this->sut = new ApplicationFinancialEvidence($this->fh, m::mock(AuthorizationService::class), $this->translator, $this->urlHelper);
    }

    public function testAlterForm()
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

        $mockSaveButton = m::mock()
            ->shouldReceive('setLabel')
            ->with('lva.external.save_and_return.link')
            ->once()
            ->shouldReceive('removeAttribute')
            ->with('class')
            ->once()
            ->shouldReceive('setAttribute')
            ->with('class', 'govuk-button govuk-button--secondary')
            ->once()
            ->getMock();

        $mockFormActions = m::mock()
            ->shouldReceive('get')
            ->with('save')
            ->andReturn($mockSaveButton)
            ->once()
            ->shouldReceive('get')
            ->with('saveAndContinue')
            ->andReturn(
                m::mock()
                ->shouldReceive('setLabel')
                ->with('lva.external.save_and_continue.button')
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('remove')
            ->with('cancel')
            ->once()
            ->getMock();

        $mockForm = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn($mockFormActions)
            ->once()
            ->shouldReceive('get')
            ->with('evidence')
            ->andReturn(
                m::mock()
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
                        m::mock()
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('get')
                    ->with('sendByPostRadio')
                    ->andReturn(
                        m::mock()
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
            ->once()
            ->getMock();

        $mockRequest = m::mock(Request::class);

        $this->fh->shouldReceive('createFormWithRequest')
            ->once()
            ->with('Lva\FinancialEvidence', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'evidence->uploadNow')
            ->once()
            ->getMock();

        $form = $this->sut->getForm($mockRequest);

        $this->assertSame($mockForm, $form);
    }
}
