<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\DashboardTmActionLink;
use Common\View\Helper\TranslateReplace;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\View\HelperPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see DashboardTmActionLink
 */
final class DashboardTmActionLinkTest extends MockeryTestCase
{
    public $sut;
    protected $urlHelper;

    protected $translator;

    protected $viewHelperManager;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->viewHelperManager = m::mock(HelperPluginManager::class);
        $this->sut = new DashboardTmActionLink($this->urlHelper, $this->viewHelperManager, $this->translator);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @return \Iterator<(int | string), array<(bool | string)>>
     *
     * @psalm-return list{array{statusId: 'tmap_st_awaiting_signature', isVariation: true, expectTextKey: 'provide-details'}, array{0: 'tmap_st_incomplete', isVariation: false, 1: 'provide-details'}, array{0: 'tmap_st_operator_signed', isVariation: false, 1: 'view-details'}, array{0: 'tmap_st_postal_application', isVariation: false, 1: 'view-details'}, array{0: 'tmap_st_tm_signed', isVariation: false, 1: 'view-details'}}
     */
    public static function dataProviderFormat(): \Iterator
    {
        yield [
            'statusId' => RefData::TMA_STATUS_AWAITING_SIGNATURE,
            'isVariation' => true,
            'expectTextKey' => 'provide-details',
        ];
        yield [
            "statusId" => RefData::TMA_STATUS_INCOMPLETE,
            'isVariation' => false,
            "expectTextKey" => 'provide-details',
        ];
        yield [
            "statusId" => RefData::TMA_STATUS_OPERATOR_SIGNED,
            'isVariation' => false,
            "expectTextKey" => 'view-details',
        ];
        yield [
            "statusId" => RefData::TMA_STATUS_POSTAL_APPLICATION,
            'isVariation' => false,
            "expectTextKey" => 'view-details',
        ];
        yield [
            "statusId" => RefData::TMA_STATUS_TM_SIGNED,
            'isVariation' => false,
            "expectTextKey" => 'view-details',
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderFormat')]
    public function testFormat($statusId, $isVariation, $expectTextKey): void
    {
        $this->translator->expects('translate')
            ->with('dashboard.tm-applications.table.action.' . $expectTextKey)
            ->andReturn('LINK TEXT');

        $mockTranslateReplace = m::mock(TranslateReplace::class);
        $mockTranslateReplace->expects('__invoke')
            ->with('dashboard.tm-applications.table.aria.' . $expectTextKey, [323])
            ->andReturn('ARIA');

        $this->viewHelperManager->shouldReceive('get')->with('translateReplace')->andReturn($mockTranslateReplace);

        $this->urlHelper->shouldReceive('fromRoute')
            ->with(
                (
                    $isVariation
                    ? 'lva-variation/transport_manager_details'
                    : 'lva-application/transport_manager_details'
                ),
                [
                    'action' => null,
                    'application' => 323,
                    'child_id' => 12.,
                ],
                [],
                true
            )
            ->andReturn('http://url.com');

        $data = [
            'applicationId' => 323,
            'transportManagerApplicationStatus' => [
                'id' => $statusId,
                'description' => 'FooBar',
            ],
            'transportManagerApplicationId' => 12,
            'isVariation' => $isVariation,
        ];
        $column = [];

        $this->assertEquals('<a class="govuk-link" href="http://url.com" aria-label="ARIA">LINK TEXT</a>', $this->sut->format($data, $column));
    }
}
