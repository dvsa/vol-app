<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\GenerateReport as GenerateReportCmd;

/**
 * @see GenerateReportCmd
 */
class GenerateReportTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $id = 'report identifier string';
        $startDate = '2019-12-25';
        $endDate = '2020-12-25';
        $user = 999;

        $sut = GenerateReportCmd::create(
            [
                'id' => $id,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'user' => $user,
            ]
        );

        static::assertEquals($id, $sut->getId());
        static::assertEquals($startDate, $sut->getStartDate());
        static::assertEquals($endDate, $sut->getEndDate());
        static::assertEquals($user, $sut->getUser());
    }
}
