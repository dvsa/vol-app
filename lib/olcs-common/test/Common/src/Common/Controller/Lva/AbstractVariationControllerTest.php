<?php

namespace CommonTest\Controller\Lva;

use Common\Controller\Lva\AbstractVariationController;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\TranslationHelperService;
use CommonTest\Bootstrap;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Processing\CreateVariationProcessingService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Variation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractVariationControllerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $mockNiTextTranslationUtil;
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $mockAuthService;
    public $mockTranslationHelper;
    public $mockProcessingCreateVariation;
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $mockFlashMessengerHelper;
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockNiTextTranslationUtil = m::mock(NiTextTranslation::class);
        $this->mockAuthService = m::mock(AuthorizationService::class);
        $this->mockTranslationHelper = m::mock(TranslationHelperService::class);
        $this->mockProcessingCreateVariation = m::mock();
        $this->mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $this->sut = m::mock(AbstractVariationController::class, [
            $this->mockNiTextTranslationUtil,
            $this->mockAuthService,
            $this->mockTranslationHelper,
            $this->mockProcessingCreateVariation,
            $this->mockFlashMessengerHelper
            ])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    /**
     * @dataProvider indexActionConditionalProvider
     */
    public function testIndexAction($conditional): void
    {
        // Mocks
        $this->mockTranslationHelper->shouldReceive('translate')
            ->andReturn('sometext');

        $mockRequest = m::mock();
        $mockForm = m::mock(\Laminas\Form\Form::class);

        // Expectations
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('render')
            ->with(
                'create-variation-confirmation',
                $mockForm,
                ['sectionText' => 'sometext']
            )
            ->andReturn('RENDER');

        $this->mockProcessingCreateVariation->shouldReceive('getForm')
            ->with($mockRequest)
            ->andReturn($mockForm);

        // @NOTE The data provider, provides multiple routes into the same if statement
        // I think this solution is quite elegant, rather than duplicating the test with all of the same expectations
        // I don't think this solution should be used on complicated units of code with multiple nested conditionals etc
        // but for units of code where there is just a single conditional with multiple routes in, I think this fits the
        // bill nicely
        $conditional($mockRequest, $mockForm);

        $this->assertEquals('RENDER', $this->sut->indexAction());
    }

    public function testIndexActionWithPost(): void
    {
        $formData = [
            'foo' => 'bar'
        ];

        $mockRequest = m::mock();
        $mockForm = m::mock(\Laminas\Form\Form::class);

        // Expectations
        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->with('licence')
            ->andReturn(123)
            ->shouldReceive('params')
            ->with('redirectRoute')
            ->andReturnNull();

        $mockForm->shouldReceive('isValid')
            ->andReturn(true);

        $this->mockProcessingCreateVariation->shouldReceive('getForm')
            ->with($mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('getDataFromForm')
            ->with($mockForm)
            ->andReturn($formData)
            ->shouldReceive('createVariation')
            ->with(123, $formData)
            ->andReturn(321);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true);

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-variation', ['application' => 321])
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    /**
     * @return (\Closure)[][]
     *
     * @psalm-return list{list{\Closure(mixed):void}, list{\Closure(mixed, mixed):void}}
     */
    public function indexActionConditionalProvider(): array
    {
        return [
            [
                static function ($mockRequest) {
                    $mockRequest->shouldReceive('isPost')
                        ->andReturn(false);
                }
            ],
            [
                static function ($mockRequest, $mockForm) {
                    $mockRequest->shouldReceive('isPost')
                        ->andReturn(true);
                    $mockForm->shouldReceive('isValid')
                        ->andReturn(false);
                }
            ]
        ];
    }
}
