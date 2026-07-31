<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\GenerateReport as GenerateReportCmd;

/**
 * @see GenerateReportCmd
 */
final class GenerateReportTest extends \PHPUnit\Framework\TestCase
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

        $this->assertEquals($id, $sut->getId());
        $this->assertEquals($startDate, $sut->getStartDate());
        $this->assertEquals($endDate, $sut->getEndDate());
        $this->assertEquals($user, $sut->getUser());
    }
}
