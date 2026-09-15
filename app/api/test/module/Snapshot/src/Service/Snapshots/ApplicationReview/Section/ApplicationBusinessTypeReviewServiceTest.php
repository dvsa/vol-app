<?php

declare(strict_types=1);

/**
 * Application Business Type Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationBusinessTypeReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Business Type Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ApplicationBusinessTypeReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new ApplicationBusinessTypeReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $data = [
            'licence' => [
                'organisation' => [
                    'type' => [
                        'description' => 'foo'
                    ]
                ]
            ]
        ];

        $expected = [
            'multiItems' => [
                [
                    [
                        'label' => 'application-review-business-type',
                        'value' => 'foo'
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }
}
