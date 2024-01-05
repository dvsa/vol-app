<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Form\Element\DynamicMultiCheckbox;
use Common\Form\Element\DynamicRadio;
use Common\Form\Element\DynamicSelect;
use Common\Service\Translator\TranslationLoader;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\I18n\View\Helper\Translate;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\FormService\Form\Lva\Stub\AbstractOverviewSubmissionStub;

/**
 * @covers Olcs\FormService\Form\Lva\AbstractOverviewSubmission
 */
class AbstractOverviewSubmissionTest extends MockeryTestCase
{
    /** @var  AbstractOverviewSubmissionStub */
    private $sut;

    /** @var  m\MockInterface */
    private $mockSm;
    /** @var  m\MockInterface | \Laminas\Form\FormInterface */
    private $mockForm;
    /** @var  m\MockInterface | \Common\Service\Helper\FormHelperService */
    private $mockFormHlp;

    private $mockTranslationHelper;

    /**
     * We can access service manager if we need to add a mock for certain applications
     *
     * @return \Laminas\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceManager()
    {
            $serviceManager =  $this->getRealServiceManager();
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
     * We can access service manager if we need to add a mock for certain applications
     *
     * @return \Laminas\ServiceManager\ServiceLocatorInterface
     */
    public function getRealServiceManager()
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
        $this->mockForm = m::mock(\Laminas\Form\FormInterface::class);

        $this->mockSm = $this->getServiceManager();

        $this->mockTranslationHelper = m::mock(Translate::class);
        $this->mockTranslationHelper
            ->shouldReceive('translateReplace')
            ->andReturnUsing(
                function ($text, $params) {
                    return '_TRLTD_' . $text . '[' . implode('|', $params) . ']';
                }
            );

        $this->mockFormHlp = m::mock(\Common\Service\Helper\FormHelperService::class)->makePartial();

        $this->sut = new AbstractOverviewSubmissionStub($this->mockTranslationHelper, $this->mockFormHlp);
    }

    public function testGetForm()
    {
        $data = ['data'];
        $params = [
            'sections' => ['unit_Sections'],
        ];

        $this->mockFormHlp
            ->shouldReceive('createForm')->once()->with('Lva\PaymentSubmission')->andReturn($this->mockForm);

        /** @var AbstractOverviewSubmissionStub | m\MockInterface $sut */
        $sut = m::mock(AbstractOverviewSubmissionStub::class . '[alterForm]', [$this->mockTranslationHelper, $this->mockFormHlp])
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('alterForm')->once()->with($this->mockForm, $data, $params)
            ->getMock();

        static::assertSame($this->mockForm, $sut->getForm($data, $params));
    }

    public function testAlterFormReadySubmitWithFee()
    {
        $data = [
            'outstandingFeeTotal' => 999,
            'disableCardPayments' => true,
        ];
        $params = [
            'actionUrl' => 'unit_ActionUrl',
            'isReadyToSubmit' => true,
        ];

        //  expect
        $mockAmountElm = m::mock(\Laminas\Form\ElementInterface::class)
            ->shouldReceive('setValue')->once()->with('_TRLTD_application.payment-submission.amount.value[999.00]')
            ->getMock();

        $mocksubmitPayElm = m::mock(\Laminas\Form\ElementInterface::class)
            ->shouldReceive('setLabel')->once()->with('submit-application.button')
            ->getMock();

        $this->mockForm
            ->shouldReceive('get')->once()->with('amount')->andReturn($mockAmountElm)
            ->shouldReceive('get')->once()->with('submitPay')->andReturn($mocksubmitPayElm)
            ->shouldReceive('setAttribute')->once()->with('action', 'unit_ActionUrl');

        //  call
        $this->sut->alterForm($this->mockForm, $data, $params);
    }

    public function testAlterFormNotReadyNoFee()
    {
        $data = [
            'outstandingFeeTotal' => -1,
            'disableCardPayments' => false,
        ];
        $params = [
            'isReadyToSubmit' => false,
        ];

        //  expect
        $mocksubmitPayElm = m::mock(\Laminas\Form\ElementInterface::class)
            ->shouldReceive('setLabel')->once()->with('submit-application.button')
            ->getMock();

        $this->mockForm
            ->shouldReceive('get')->never()->with('amount')
            ->shouldReceive('get')->once()->with('submitPay')->andReturn($mocksubmitPayElm);

        $this->mockFormHlp
            ->shouldReceive('remove')->once()->with($this->mockForm, 'amount')
            ->shouldReceive('remove')->once()->with($this->mockForm, 'submitPay');

        //  call
        $this->sut->alterForm($this->mockForm, $data, $params);
    }
}
