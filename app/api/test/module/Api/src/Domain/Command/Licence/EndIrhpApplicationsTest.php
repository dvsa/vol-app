<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Licence;

use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpApplications;
use Dvsa\Olcs\Api\Entity\WithdrawableInterface;
use PHPUnit\Framework\TestCase;

/**
 * EndIrhpApplications test
 */
final class EndIrhpApplicationsTest extends TestCase
{
    public function testStructure(): void
    {
        $id = 100;
        $reason = WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED;

        $sut = EndIrhpApplications::create(
            [
                'id' => $id,
                'reason' => $reason,
            ]
        );

        $this->assertEquals($id, $sut->getId());
        $this->assertEquals($reason, $sut->getReason());
        $this->assertEquals([
            'id' => $id,
            'reason' => $reason,
        ], $sut->getArrayCopy());
    }
}
