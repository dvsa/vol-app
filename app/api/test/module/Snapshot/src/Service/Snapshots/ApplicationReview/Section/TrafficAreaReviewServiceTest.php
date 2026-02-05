<?php

declare(strict_types=1);

/**
 * Traffic Area Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\TrafficAreaReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Traffic Area Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TrafficAreaReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new TrafficAreaReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $data = [
            'licence' => [
                'trafficArea' => [
                    'name' => 'foo'
                ]
            ]
        ];

        $expected = [
            'header' => 'review-operating-centres-traffic-area-title',
            'multiItems' => [
                [
                    [
                        'label' => 'review-operating-centres-traffic-area',
                        'value' => 'foo'
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }
}
