<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Processing;

use Dvsa\Olcs\Transfer\Query\Processing\History;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\Processing\History::class)]
final class HistoryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = History::create(
            [
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
                'irhpApplication' => 100,
                'case' => 200,
                'licence' => 300,
                'organisation' => 400,
                'transportManager' => 500,
                'user' => 600,
                'application' => 700,
            ]
        );
        $this->assertEquals([
            'page' => 1,
            'limit' => 10,
            'sort' => 'id',
            'order' => 'ASC',
            'sortWhitelist' => [],
            'irhpApplication' => 100,
            'case' => 200,
            'licence' => 300,
            'organisation' => 400,
            'transportManager' => 500,
            'user' => 600,
            'application' => 700,
        ], $sut->getArrayCopy());
    }
}
