<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\IrhpPermitBundle;
use Dvsa\Olcs\Api\Service\Document\Bookmark\IrhpExpiryDate;

/**
 * Class IrhpExpiryDateTest
 *
 * @author Henry White <henry.white@capgemini.com>
 */
class IrhpExpiryDateTest extends \PHPUnit\Framework\TestCase
{
    public const SUT_CLASS_NAME = IrhpExpiryDate::class;

    public function testGetQuery(): void
    {
        $bookmark = new IrhpExpiryDate();
        $query = $bookmark->getQuery([IrhpExpiryDate::SRCH_VAL_KEY => 123]);
        $this->assertInstanceOf(IrhpPermitBundle::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRenderProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = new IrhpExpiryDate();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function dpRenderProvider(): array
    {
        return [
            'no expiry date, use stock end date' => [
                [
                    'irhpPermitRange' => [
                        'irhpPermitStock' => [
                            'validTo' => '2021-12-25',
                        ],
                    ],
                ],
                '25 December 2021'
            ],
            'expiry date only' => [
                [
                    'expiryDate' => '2021-12-31',
                ],
                '31 December 2021',
            ],
            'expiry date used in preference to stock end date' => [
                [
                    'irhpPermitRange' => [
                        'irhpPermitStock' => [
                            'validTo' => '2021-12-25',
                        ],
                    ],
                    'expiryDate' => '2021-12-31',
                ],
                '31 December 2021'
            ],
        ];
    }
}
