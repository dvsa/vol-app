<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Command\Bus\CreateNoticePeriod;

/**
 * @see CreateNoticePeriod
 */
class CreateNoticePeriodTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $noticeArea = 'notice area';
        $standardPeriod = 123;

        $data = [
            'noticeArea' => $noticeArea,
            'standardPeriod' => $standardPeriod,
        ];

        $command = CreateNoticePeriod::create($data);

        $this->assertEquals($noticeArea, $command->getNoticeArea());
        $this->assertEquals($standardPeriod, $command->getStandardPeriod());
    }
}
