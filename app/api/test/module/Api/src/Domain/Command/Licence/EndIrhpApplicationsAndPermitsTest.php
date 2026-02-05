<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Licence;

use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpApplicationsAndPermits;
use Dvsa\Olcs\Api\Entity\WithdrawableInterface;
use PHPUnit\Framework\TestCase;

/**
 * EndIrhpApplicationsAndPermits test
 */
class EndIrhpApplicationsAndPermitsTest extends TestCase
{
    public function testStructure(): void
    {
        $id = 100;
        $reason = WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED;
        $context = EndIrhpApplicationsAndPermits::CONTEXT_REVOKE;

        $sut = EndIrhpApplicationsAndPermits::create(
            [
                'id' => $id,
                'reason' => $reason,
                'context' => $context,
            ]
        );

        static::assertEquals($id, $sut->getId());
        static::assertEquals($reason, $sut->getReason());
        static::assertEquals($context, $sut->getContext());
        static::assertEquals(
            [
                'id' => $id,
                'reason' => $reason,
                'context' => $context,
            ],
            $sut->getArrayCopy()
        );
    }
}
