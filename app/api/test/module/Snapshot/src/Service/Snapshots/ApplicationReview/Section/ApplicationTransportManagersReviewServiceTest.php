<?php

declare(strict_types=1);

/**
 * Application Transport Managers Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationTransportManagersReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\TransportManagersReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Transport Managers Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationTransportManagersReviewServiceTest extends MockeryTestCase
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

        $this->sut = new ApplicationTransportManagersReviewService(
            $abstractReviewServiceServices,
            $this->mockTm
        );
    }

    public function testGetConfigFromData(): void
    {
        $data = ['transportManagers' => ['bar' => 'foo']];

        $expected = [
            'subSections' => [
                [
                    'mainItems' => ['foo' => 'bar']
                ]
            ]
        ];

        $this->mockTm->shouldReceive('getConfigFromData')
            ->with(['bar' => 'foo'])
            ->andReturn(['foo' => 'bar']);

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }
}
