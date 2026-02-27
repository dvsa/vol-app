<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\FStandingProvedDate;

/**
 * FStandingProvedDate test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FStandingProvedDateTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new FStandingProvedDate();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('expiryDateProvider')]
    public function testRender(mixed $expiredDate, mixed $expected): void
    {
        $bookmark = new FStandingProvedDate();
        $bookmark->setData(['expiryDate' => $expiredDate]);
        $this->assertEquals($expected, $bookmark->render());
    }

    public static function expiryDateProvider(): array
    {
        return [
            ['2016-03-31', '29/02/2016'],
            ['2016-02-29', '31/01/2016'],
            ['2016-01-31', '31/12/2015'],
            [null, '']
        ];
    }
}
