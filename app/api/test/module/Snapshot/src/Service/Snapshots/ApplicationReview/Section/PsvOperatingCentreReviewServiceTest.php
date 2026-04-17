<?php

declare(strict_types=1);

/**
 * Psv Operating Centre Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\PsvOperatingCentreReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Psv Operating Centre Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvOperatingCentreReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new PsvOperatingCentreReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $data = [
            'noOfVehiclesRequired' => 10,
            'permission' => 'N',
            'operatingCentre' => [
                'address' => [
                    'addressLine1' => 'Some building',
                    'addressLine2' => 'Foo street',
                    'town' => 'Bartown',
                    'postcode' => 'FB1 1FB'
                ]
            ]
        ];
        $expected = [
            'header' => 'Some building, Bartown',
            'multiItems' => [
                [
                    [
                        'label' => 'review-operating-centre-address',
                        'value' => 'Some building, Foo street, Bartown, FB1 1FB'
                    ]
                ],
                'vehicles+trailers' => [
                    [
                        'label' => 'review-operating-centre-total-vehicles',
                        'value' => 10
                    ]
                ],
                [
                    [
                        'label' => 'review-operating-centre-permission',
                        'value' => 'Unconfirmed'
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }
}
