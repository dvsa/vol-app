<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\ContNextExpDate;

/**
 * Continuation Next Expiry Date test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ContNextExpDateTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new ContNextExpDate();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoExpiryDate(): void
    {
        $bookmark = new ContNextExpDate();
        $bookmark->setData(
            [
                'expiryDate' => null
            ]
        );

        $this->assertEquals(
            '',
            $bookmark->render()
        );
    }

    public function testRenderWithContNextExpirtyDate(): void
    {
        $bookmark = new ContNextExpDate();
        $bookmark->setData(
            [
                'expiryDate' => '2014-01-01'
            ]
        );

        $this->assertEquals(
            '01/01/2019',
            $bookmark->render()
        );
    }
}
