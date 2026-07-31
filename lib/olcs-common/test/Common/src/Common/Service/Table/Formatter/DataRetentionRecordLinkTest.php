<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\DataRetentionRecordLink;
use Common\View\Helper\Status as StatusHelper;
use Laminas\View\HelperPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * DataRetentionRecord Link test
 */
final class DataRetentionRecordLinkTest extends TestCase
{
    private const string ORGANISATION_NAME = 'DVSA';

    private const string ORGANISATION_ID = 'ORG123';

    private const string LIC_NO = 'OB1234';

    private const int LICENCE_ID = 9;

    private const int ENTITY_ID = 9;

    protected $urlHelper;

    protected $viewHelperManager;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->viewHelperManager = m::mock(HelperPluginManager::class);
        $this->sut = new DataRetentionRecordLink($this->urlHelper, $this->viewHelperManager);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @param array  $queryData           Query Data
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('entityTypeDataProviderWithUrl')]
    public function testFormat($queryData, $statusArray): void
    {
        $queryData = array_merge(
            [
                'organisationName' => self::ORGANISATION_NAME,
                'organisationId' => self::ORGANISATION_ID,
                'licNo' => self::LIC_NO,
                'licenceId' => self::LICENCE_ID,
                'entityPk' => self::ENTITY_ID,
            ],
            $queryData
        );

        $statusLabel = 'status label';

        $this->getUrlHelperMock();
        $this->getViewHelperWithStatusMock($statusArray, $statusLabel);

        $this->assertEquals(
            '<a class="govuk-link" href="DATA_RETENTION_RECORD_URL" target="_self">' . $queryData['organisationName'] . '</a> / ' .
            '<a class="govuk-link" href="DATA_RETENTION_RECORD_URL" target="_self">' . $queryData['licNo'] . '</a> / ' .
            '<a class="govuk-link" href="DATA_RETENTION_RECORD_URL" target="_self">' .
            ucfirst((string) $queryData['entityName']) . ' ' . $queryData['entityPk'] .
            '</a>' .
            $statusLabel,
            $this->sut->format($queryData, [])
        );
    }

    /**
     * Parameter 1: query data
     * Parameter 2: URL parameters for last entity
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function entityTypeDataProviderWithUrl(): \Iterator
    {
        yield 'Licence entity type' => [
            [
                'entityName' => 'licence',
                'actionConfirmation' => false,
                'nextReviewDate' => '2030-12-25'
            ],
            DataRetentionRecordLink::STATUS_POSTPONED
        ];
        yield 'Application entity type' => [
            [
                'entityName' => 'application',
                'actionConfirmation' => true,
                'nextReviewDate' => '2030-12-25'
            ],
            DataRetentionRecordLink::STATUS_DELETION
        ];
        yield 'Transport manager entity type' => [
            [
                'entityName' => 'transport_manager',
                'actionConfirmation' => false,
                'nextReviewDate' => null
            ],
            DataRetentionRecordLink::STATUS_REVIEW
        ];
        yield 'IRFO GV entity type' => [
            [
                'entityName' => 'irfo_gv_permit',
                'actionConfirmation' => false,
                'nextReviewDate' => '2030-12-25'
            ],
            DataRetentionRecordLink::STATUS_POSTPONED
        ];
        yield 'IRFO PSV auth entity type' => [
            [
                'entityName' => 'irfo_psv_auth',
                'actionConfirmation' => true,
                'nextReviewDate' => '2030-12-25'
            ],
            DataRetentionRecordLink::STATUS_DELETION
        ];
        yield 'Organisation entity type' => [
            [
                'entityName' => 'organisation',
                'actionConfirmation' => false,
                'nextReviewDate' => null
            ],
            DataRetentionRecordLink::STATUS_REVIEW
        ];
        yield 'Case entity type' => [
            [
                'entityName' => 'cases',
                'actionConfirmation' => false,
                'nextReviewDate' => '2030-12-25'
            ],
            DataRetentionRecordLink::STATUS_POSTPONED
        ];
        yield 'Licence entity type to review' => [
            [
                'entityName' => 'licence',
                'actionConfirmation' => false,
                'nextReviewDate' => new \DateTime()->format('Y-m-d')
            ],
            DataRetentionRecordLink::STATUS_REVIEW
        ];
    }

    public function testWithoutLicenceNumberAndUndefinedEntity(): void
    {
        $statusLabel = 'statusLabel';

        $this->getViewHelperWithStatusMock(DataRetentionRecordLink::STATUS_REVIEW, $statusLabel);

        $queryData = [
            'entityName' => 'undefined',
            'organisationId' => self::ORGANISATION_ID,
            'organisationName' => 'DVSA',
            'entityPk' => self::ENTITY_ID,
            'licenceId' => self::LICENCE_ID,
            'licNo' => '123',
            'actionConfirmation' => false,
            'nextReviewDate' => null
        ];

        $this->assertEquals(
            $queryData['organisationName'] . ' / ' .
            $queryData['licNo'] . ' / ' .
            $queryData['entityName'] . ' ' .
            $queryData['entityPk'] .
            $statusLabel,
            $this->sut->format($queryData, [])
        );
    }

    public function testWithoutLicenceNumberAndOrganisationAndUndefinedEntity(): void
    {
        $statusLabel = 'statusLabel';

        $this->getViewHelperWithStatusMock(DataRetentionRecordLink::STATUS_REVIEW, $statusLabel);

        $queryData = [
            'entityName' => 'undefined',
            'organisationName' => 'org',
            'organisationId' => self::ORGANISATION_ID,
            'entityPk' => self::ENTITY_ID,
            'licenceId' => self::LICENCE_ID,
            'licNo' => '123',
            'actionConfirmation' => false,
            'nextReviewDate' => null
        ];

        $this->assertEquals(
            $queryData['organisationName'] . ' / ' .
            $queryData['licNo'] . ' / ' .
            $queryData['entityName'] . ' ' .
            $queryData['entityPk'] .
            $statusLabel,
            $this->sut->format($queryData, [])
        );
    }

    /**
     * @param string[] $statusArray
     *
     * @psalm-param 'status label'|'statusLabel' $statusLabel
     * @psalm-param array{value: 'To review', colour: 'green'} $statusArray
     */
    private function getViewHelperWithStatusMock(array $statusArray, string $statusLabel): void
    {
        $mockStatusHelper = m::mock(StatusHelper::class);
        $mockStatusHelper->shouldReceive('__invoke')
            ->once()
            ->with($statusArray)
            ->andReturn($statusLabel);

        $this->viewHelperManager->shouldReceive('get')->with('status')->andReturn($mockStatusHelper);
    }

    private function getUrlHelperMock(): void
    {
        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with(
                'licence-no',
                ['licNo' => self::LIC_NO],
                [],
                true
            )
            ->andReturn('DATA_RETENTION_RECORD_URL')
            ->shouldReceive('fromRoute')
            ->with(
                'licence',
                ['licence' => self::LICENCE_ID],
                [],
                true
            )
            ->andReturn('DATA_RETENTION_RECORD_URL')
            ->shouldReceive('fromRoute')
            ->with(
                'lva-application',
                ['application' => self::ENTITY_ID],
                [],
                true
            )
            ->andReturn('DATA_RETENTION_RECORD_URL')
            ->shouldReceive('fromRoute')
            ->with(
                'transport-manager',
                ['transportManager' => self::ENTITY_ID],
                [],
                true
            )
            ->andReturn('DATA_RETENTION_RECORD_URL')
            ->shouldReceive('fromRoute')
            ->with(
                'operator/business-details',
                ['organisation' => self::ORGANISATION_ID],
                [],
                true
            )
            ->andReturn('DATA_RETENTION_RECORD_URL')
            ->shouldReceive('fromRoute')
            ->with(
                'operator/irfo/gv-permits',
                [
                    'organisation' => self::ORGANISATION_ID,
                    'action' => 'details',
                    'id' => self::ENTITY_ID,
                ],
                [],
                true
            )
            ->andReturn('DATA_RETENTION_RECORD_URL')
            ->shouldReceive('fromRoute')
            ->with(
                'operator/irfo/psv-authorisations',
                [
                    'organisation' => self::ORGANISATION_ID,
                    'action' => 'edit',
                    'id' => self::ENTITY_ID,
                ],
                [],
                true
            )
            ->andReturn('DATA_RETENTION_RECORD_URL')
            ->shouldReceive('fromRoute')
            ->with(
                'case',
                [
                    'action' => 'details',
                    'case' => self::ENTITY_ID,
                ],
                [],
                true
            )
            ->andReturn('DATA_RETENTION_RECORD_URL')
            ->getMock();
    }
}
