<?php

declare(strict_types=1);

/**
 * Queue Complete command test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Queue;

use Dvsa\Olcs\Api\Domain\Command\Queue\Complete;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * Queue Complete command test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompleteTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $item = new QueueEntity();
        $command = Complete::create(['item' => $item]);

        $this->assertSame($item, $command->getItem());
        $this->assertEquals(['item' => $item], $command->getArrayCopy());
    }
}
