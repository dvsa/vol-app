<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva;

use Common\Service\Cqrs\Response as CqrsResponse;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\AbstractFinancialEvidenceAssessmentController;
use Olcs\Controller\Lva\Application\FinancialEvidenceAssessmentController as ApplicationController;
use Olcs\Controller\Lva\Factory\Controller\FinancialEvidenceAssessmentControllerFactory;
use Olcs\Controller\Lva\Licence\FinancialEvidenceAssessmentController as LicenceController;
use Olcs\Controller\Lva\Variation\FinancialEvidenceAssessmentController as VariationController;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;

/**
 * Behaviour shared by the licence, application and variation assessment pages.
 */
class FinancialEvidenceAssessmentControllerTest extends MockeryTestCase
{
    /**
     * A variation is an application in the API, so only the licence page scopes by licence.
     */
    public static function contextProvider(): \Iterator
    {
        yield 'licence' => [LicenceController::class, 'licence'];
        yield 'application' => [ApplicationController::class, 'application'];
        yield 'variation' => [VariationController::class, 'application'];
    }

    #[DataProvider('contextProvider')]
    public function testAnalysesAreScopedToTheLvaContextAndSuccessOnly(string $class, string $expectedKey): void
    {
        $sut = $this->createSut($class);
        $sut->expects('getIdentifier')->andReturn(42);

        $sut->expects('handleQuery')
            ->with(m::on(static function ($query) use ($expectedKey): bool {
                if (!$query instanceof DocumentAnalysisList) {
                    return false;
                }

                $otherKey = $expectedKey === 'licence' ? 'application' : 'licence';

                // Asserting the other scope is absent stops a query leaking across contexts.
                return (int) $query->{'get' . ucfirst($expectedKey)}() === 42
                    && $query->{'get' . ucfirst($otherKey)}() === null
                    && $query->getStatus() === 'SUCCESS';
            }))
            ->andReturn($this->okResponse([]));

        $sut->expects('render')->andReturnUsing(static fn(ViewModel $view) => $view);

        $view = $sut->indexAction();

        $this->assertSame([], $view->getVariable('tabs'));
        $this->assertFalse($view->getVariable('hasDocuments'));
    }

    /**
     * Tabs come solely from successful analyses, newest completion first, with the date carried
     * on each analysis. No document list is queried.
     */
    public function testTabsAreBuiltFromAnalysesOnly(): void
    {
        $sut = $this->createSut(LicenceController::class);
        $sut->allows('getIdentifier')->andReturn(42);

        $sut->expects('handleQuery')
            ->once()
            ->andReturn($this->okResponse([
                ['id' => 1, 'documentId' => 11, 'documentDate' => '2026-01-02 00:00:00', 'completedAt' => '2026-01-03 00:00:00'],
                ['id' => 2, 'documentId' => 12, 'documentDate' => '2026-02-02 00:00:00', 'completedAt' => '2026-02-03 00:00:00'],
                ['id' => 3, 'documentId' => 13, 'documentDate' => null, 'completedAt' => '2025-12-01 00:00:00'],
            ]));

        $sut->expects('render')->andReturnUsing(static fn(ViewModel $view) => $view);

        $view = $sut->indexAction();
        $tabs = $view->getVariable('tabs');

        $this->assertTrue($view->getVariable('hasDocuments'));
        $this->assertSame(['latest', 'analysis-1', 'analysis-3'], array_column($tabs, 'id'));
        $this->assertSame(['Latest', '02/01/2026', 'Unknown date'], array_column($tabs, 'label'));
        $this->assertSame('02/02/2026', $tabs[0]['date']);
    }

    /** A failed API call shows no tabs rather than erroring the page. */
    public function testFailedAnalysisQueryShowsNoTabs(): void
    {
        $sut = $this->createSut(ApplicationController::class);
        $sut->allows('getIdentifier')->andReturn(42);

        $response = m::mock(CqrsResponse::class);
        $response->allows('isOk')->andReturnFalse();
        $sut->expects('handleQuery')->andReturn($response);
        $sut->expects('render')->andReturnUsing(static fn(ViewModel $view) => $view);

        $this->assertSame([], $sut->indexAction()->getVariable('tabs'));
    }

    #[DataProvider('contextProvider')]
    public function testFactoryCreatesEachContext(string $class, string $scopeKey): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->allows('get')->with(NiTextTranslation::class)->andReturn(m::mock(NiTextTranslation::class));
        $container->allows('get')->with(AuthorizationService::class)->andReturn(m::mock(AuthorizationService::class));
        $container->allows('get')->with(StringHelperService::class)->andReturn(new StringHelperService());
        $container->allows('get')->with(RestrictionHelperService::class)->andReturn(m::mock(RestrictionHelperService::class));
        $container->allows('get')->with(FlashMessengerHelperService::class)->andReturn(m::mock(FlashMessengerHelperService::class));
        $container->allows('get')->with('navigation')->andReturn([]);

        $controller = (new FinancialEvidenceAssessmentControllerFactory())($container, $class);

        $this->assertInstanceOf($class, $controller);
        // Proves each concrete controller's $lva maps to the scope the API query will use.
        $this->assertSame(
            $scopeKey,
            (new \ReflectionMethod($controller, 'getIdentifierIndex'))->invoke($controller)
        );
    }

    public function testFactoryRejectsUnrelatedClasses(): void
    {
        $this->expectException(ServiceNotCreatedException::class);

        (new FinancialEvidenceAssessmentControllerFactory())(m::mock(ContainerInterface::class), \stdClass::class);
    }

    /**
     * @param class-string<AbstractFinancialEvidenceAssessmentController> $class
     */
    private function createSut(string $class): AbstractFinancialEvidenceAssessmentController|m\MockInterface
    {
        return m::mock($class, [
            m::mock(NiTextTranslation::class),
            m::mock(AuthorizationService::class),
            new StringHelperService(),
            m::mock(RestrictionHelperService::class),
            m::mock(FlashMessengerHelperService::class),
            [],
        ])->makePartial()->shouldAllowMockingProtectedMethods();
    }

    private function okResponse(array $analyses): CqrsResponse
    {
        $response = m::mock(CqrsResponse::class);
        $response->allows('isOk')->andReturnTrue();
        $response->allows('getResult')->andReturn(['analyses' => $analyses]);

        return $response;
    }
}
