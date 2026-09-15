<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Licence;

use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpApplicationsAndPermits;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpPermits;
use PHPUnit\Framework\TestCase;

/**
 * EndIrhpPermits test
 */
final class EndIrhpPermitsTest extends TestCase
{
    public function testStructure(): void
    {
        $id = 100;
        $context = EndIrhpApplicationsAndPermits::CONTEXT_REVOKE;

        $sut = EndIrhpPermits::create(
            [
                'id' => $id,
                'context' => $context,
            ]
        );

        $this->assertEquals($id, $sut->getId());
        $this->assertEquals($context, $sut->getContext());
        $this->assertEquals([
            'id' => $id,
            'context' => $context,
        ], $sut->getArrayCopy());
    }
}
