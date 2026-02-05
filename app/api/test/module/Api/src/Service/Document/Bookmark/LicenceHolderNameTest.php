<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\LicenceHolderName;

/**
 * Licence holder name test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceHolderNameTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new LicenceHolderName();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new LicenceHolderName();
        $bookmark->setData(
            [
                'organisation' => [
                    'name' => 'Org 1'
                ]
            ]
        );

        $this->assertEquals(
            'Org 1',
            $bookmark->render()
        );
    }

    public function testRenderWithTradingNames(): void
    {
        $bookmark = new LicenceHolderName();
        $bookmark->setData(
            [
                'organisation' => [
                    'name' => 'Org 1',
                ],
                'tradingNames' => [
                    [
                        'id' => 1,
                        'name' => 'Alias 1',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Alias 2',
                    ],
                ],
            ]
        );

        $this->assertEquals(
            "Org 1\nT/A Alias 1, Alias 2",
            $bookmark->render()
        );
    }

    public function testRenderWithTradingNamesTruncated(): void
    {
        $bookmark = new LicenceHolderName();
        $bookmark->setData(
            [
                'organisation' => [
                    'name' => 'Org 1',
                ],
                'tradingNames' => [
                    [
                        'id' => 1,
                        'name' => 'Quite Long Alias 1',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Quite Long Alias 2',
                    ],
                    [
                        'id' => 3,
                        'name' => 'Quite Long Alias 3',
                    ],
                ],
            ]
        );

        $this->assertEquals(
            "Org 1\nT/A Quite Long Alias 1, Quite Long Alia",
            $bookmark->render()
        );
    }
}
