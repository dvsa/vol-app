<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Audit;

use Dvsa\Olcs\Transfer\Query\Audit\ReadIrhpApplication;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\Audit\ReadIrhpApplication::class)]
final class ReadIrhpApplicationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ReadIrhpApplication::create(
            [
                'id' => 2,
                'page' => 1,
                'limit' => 10,
            ]
        );
        $this->assertEquals([
            'id' => 2,
            'page' => 1,
            'limit' => 10,
        ], $sut->getArrayCopy());
    }
}
