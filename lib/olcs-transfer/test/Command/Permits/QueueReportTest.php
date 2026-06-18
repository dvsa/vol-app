<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Permits;

use Dvsa\Olcs\Transfer\Command\Permits\QueueReport;

/**
 * @see QueueReport
 */
class QueueReportTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $id = 'cert_roadworthiness';
        $startDate = '2021-12-25';
        $endDate = '2021-12-31';

        $data = [
            'id' => $id,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        $command = QueueReport::create($data);

        $this->assertEquals($id, $command->getId());
        $this->assertEquals($startDate, $command->getStartDate());
        $this->assertEquals($endDate, $command->getEndDate());
    }
}
