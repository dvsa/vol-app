<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\CompanyStatus;
use PHPUnit\Framework\TestCase;

final class CompanyStatusTest extends TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new CompanyStatus();
        $query = $bookmark->getQuery(['licence' => 123, 'bundle' => []]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dptestRender')]
    public function testRender(mixed $companyStatus): void
    {
        $bookmark = new CompanyStatus();
        $bookmark->setData(
            ['companyStatus' => $companyStatus['status']]
        );

        $this->assertEquals(
            $companyStatus['expected'],
            $bookmark->render()
        );
    }

    public static function dptestRender(): \Iterator
    {
        yield [['status' => 'liquidation', 'expected' => 'Liquidation']];
        yield [['status' => 'insolvency-proceedings', 'expected' => 'Insolvency Proceedings']];
        yield [['status' => 'administration', 'expected' => 'Administration']];
        yield [['status' => 'liquidation', 'expected' => 'Liquidation']];
        yield [['status' => 'receivership', 'expected' => 'Receivership']];
        yield [['status' => 'voluntary-arrangement', 'expected' => 'Voluntary Arrangement']];
        yield [['status' => null, 'expected' => null]];
    }
}
