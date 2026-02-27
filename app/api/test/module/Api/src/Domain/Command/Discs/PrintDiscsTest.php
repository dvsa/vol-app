<?php

declare(strict_types=1);

/**
 * Print Discs Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Discs;

use Dvsa\Olcs\Api\Domain\Command\Discs\PrintDiscs;

/**
 * Print Discs Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrintDiscsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = PrintDiscs::create(
            [
                'discs' => ['d1', 'd2'],
                'type' => 'gv',
                'startNumber' => 1,
                'user' => 2
            ]
        );

        $this->assertEquals(['d1', 'd2'], $command->getDiscs());
        $this->assertEquals('gv', $command->getType());
        $this->assertEquals(1, $command->getStartNumber());
        $this->assertEquals(2, $command->getUser());
    }
}
