<?php

declare(strict_types=1);

/**
 * Variation Transport Managers Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\TransportManagersReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationTransportManagersReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Transport Managers Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationTransportManagersReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var TransportManagersReviewService */
    protected $mockTm;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->mockTm = m::mock(TransportManagersReviewService::class);

        $this->sut = new VariationTransportManagersReviewService(
            $abstractReviewServiceServices,
            $this->mockTm
        );
    }

    public function testGetConfigFromDataOneOfEach(): void
    {
        $tm1 = [
            'action' => 'A',
            'foo' => 'A'
        ];

        $tm2 = [
            'action' => 'U',
            'foo' => 'U'
        ];

        $tm3 = [
            'action' => 'D',
            'foo' => 'D'
        ];

        $data = [
            'transportManagers' => [
                $tm1, $tm2, $tm3
            ]
        ];

        $expected = [
            'subSections' => [
                [
                    'title' => 'review-transport-manager-added-title',
                    'mainItems' => ['foo' => 'bar']
                ],
                [
                    'title' => 'review-transport-manager-updated-title',
                    'mainItems' => ['foo' => 'bar']
                ],
                [
                    'title' => 'review-transport-manager-deleted-title',
                    'mainItems' => ['foo' => 'bar']
                ]
            ]
        ];

        $this->mockTm->shouldReceive('getConfigFromData')
            ->once()
            ->with([$tm1])
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('getConfigFromData')
            ->once()
            ->with([$tm2])
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('getConfigFromData')
            ->once()
            ->with([$tm3])
            ->andReturn(['foo' => 'bar']);

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public function testGetConfigFromDataMultipleAndNone(): void
    {
        $tm1 = [
            'action' => 'A',
            'foo' => 'A'
        ];

        $tm2 = [
            'action' => 'A',
            'foo' => 'A'
        ];

        $tm3 = [
            'action' => 'D',
            'foo' => 'D'
        ];

        $data = [
            'transportManagers' => [
                $tm1, $tm2, $tm3
            ]
        ];

        $expected = [
            'subSections' => [
                [
                    'title' => 'review-transport-manager-added-title',
                    'mainItems' => ['foo' => 'bar']
                ],
                [
                    'title' => 'review-transport-manager-deleted-title',
                    'mainItems' => ['foo' => 'bar']
                ]
            ]
        ];

        $this->mockTm->shouldReceive('getConfigFromData')
            ->once()
            ->with([$tm1, $tm2])
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('getConfigFromData')
            ->once()
            ->with([$tm3])
            ->andReturn(['foo' => 'bar']);

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }
}
