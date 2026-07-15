<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\Document\PrintLetter;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\Document\PrintLetter::class)]
final class PrintLetterTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $qry = PrintLetter::create(
            [
                'id' => 'unit_id',
            ]
        );

        $this->assertEquals('unit_id', $qry->getId());
    }
}
