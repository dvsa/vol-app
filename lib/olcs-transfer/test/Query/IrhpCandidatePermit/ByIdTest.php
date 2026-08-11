<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\ById;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\ById::class)]
final class ByIdTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ById::create(
            [
              'id' => 2,
            ]
        );
        $this->assertEquals([
        'id' => 2
        ], $sut->getArrayCopy());
    }
}
