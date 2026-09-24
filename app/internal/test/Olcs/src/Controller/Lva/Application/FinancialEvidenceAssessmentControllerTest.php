<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva\Application;

use Common\Controller\Plugin\FeaturesEnabled;
use Common\RefData;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Controller\Plugin\CreateHttpNotFoundModel;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Application\FinancialEvidenceAssessmentController;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;

class FinancialEvidenceAssessmentControllerTest extends MockeryTestCase
{
    private FinancialEvidenceAssessmentController $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = m::mock(FinancialEvidenceAssessmentController::class, [
            m::mock(NiTextTranslation::class),
            m::mock(AuthorizationService::class),
            new StringHelperService(),
            m::mock(RestrictionHelperService::class),
            m::mock(FlashMessengerHelperService::class),
            [],
        ])->makePartial()->shouldAllowMockingProtectedMethods();
    }

    #[DataProvider('dispatchProvider')]
    public function testDispatchRequiresIdp(bool $enabled, bool $skipPreDispatch): void
    {
        $querySender = m::mock(QuerySender::class);
        $querySender->shouldReceive('featuresEnabled')->with(['idp'])->once()->andReturn($enabled);
        $this->sut->shouldReceive('plugin')
            ->with('featuresEnabled')
            ->once()
            ->andReturn(new FeaturesEnabled($querySender));

        $event = new MvcEvent();
        // The feature gate must still apply when normal pre-dispatch checks are skipped.
        $event->setRouteMatch(new RouteMatch(['action' => 'index', 'skipPreDispatch' => $skipPreDispatch]));
        $event->setResponse(new HttpResponse());
        $this->sut->setEvent($event);

        if ($enabled) {
            $view = new ViewModel();
            $this->sut->shouldReceive('maybeTranslateForNi')->once();
            $this->sut->shouldReceive('indexAction')->once()->andReturn($view);
            $this->sut->shouldNotReceive('notFoundAction');
            if (!$skipPreDispatch) {
                $this->sut->shouldReceive('preDispatch')->once()->andReturnNull();
            } else {
                $this->sut->shouldNotReceive('preDispatch');
            }
        } else {
            $this->sut->shouldNotReceive('maybeTranslateForNi');
            $this->sut->shouldNotReceive('preDispatch');
            $this->sut->shouldNotReceive('indexAction');
            $this->sut->shouldReceive('plugin')
                ->with('createHttpNotFoundModel')
                ->once()
                ->andReturn(new CreateHttpNotFoundModel());
        }

        $response = $this->sut->onDispatch($event);

        self::assertSame($response, $event->getResult());
        self::assertSame($enabled ? 200 : 404, $event->getResponse()->getStatusCode());
        if ($enabled) {
            self::assertSame($view, $response);
        }
    }

    /**
     * Visibility is decided by the API (SectionAccessService gates on IDP), so the nav
     * must render the section whenever the API returns it, without its own toggle check.
     */
    public function testNavigationShowsSectionWhenApiReturnsIt(): void
    {
        $this->sut->shouldNotReceive('handleQuery');
        $this->expectNavigationData(true);

        $sections = (new ReflectionMethod($this->sut, 'getSectionsForView'))->invoke($this->sut);

        self::assertArrayHasKey('overview', $sections);
        self::assertArrayHasKey('business_details', $sections);
        self::assertSame(
            'lva-application/financial_evidence_assessment',
            $sections['financial_evidence_assessment']['route']
        );
        self::assertSame('complete', $sections['financial_evidence_assessment']['class']);
    }

    public function testNavigationDoesNotQueryToggleWhenSectionIsAbsent(): void
    {
        $this->sut->shouldNotReceive('handleQuery');
        $this->expectNavigationData(false);

        $sections = (new ReflectionMethod($this->sut, 'getSectionsForView'))->invoke($this->sut);

        self::assertArrayNotHasKey('financial_evidence_assessment', $sections);
        self::assertArrayHasKey('business_details', $sections);
    }

    public static function dispatchProvider(): array
    {
        return [
            'enabled' => [true, false],
            'enabled with pre-dispatch skipped' => [true, true],
            'disabled' => [false, false],
            'disabled with pre-dispatch skipped' => [false, true],
        ];
    }


    private function expectNavigationData(bool $includeAssessment): void
    {
        $completion = [
            'businessDetailsStatus' => RefData::APPLICATION_COMPLETION_STATUS_COMPLETE,
            'financialEvidenceAssessmentStatus' => RefData::APPLICATION_COMPLETION_STATUS_COMPLETE,
        ];
        $accessibleSections = ['business_details' => ['enabled' => true]];
        if ($includeAssessment) {
            $accessibleSections['financial_evidence_assessment'] = ['enabled' => true];
        }

        $this->sut->shouldReceive('getApplicationId')->once()->andReturn(42);
        $this->sut->shouldReceive('getApplicationData')->with(42)->once()->andReturn([
            'applicationCompletion' => $completion,
            'status' => ['id' => 'apsts_consideration'],
            'goodsOrPsv' => ['id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE],
            'vehicleType' => ['id' => RefData::APP_VEHICLE_TYPE_HGV],
        ]);
        $this->sut->shouldReceive('getAccessibleSections')->with(false)->once()->andReturn($accessibleSections);
        $this->sut->shouldReceive('setEnabledAndCompleteFlagOnSections')
            ->with($accessibleSections, $completion)
            ->once()
            ->andReturn($accessibleSections);
    }
}
