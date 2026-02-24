<?php

declare(strict_types=1);

/**
 * Queue Create command test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Queue;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create;

/**
 * Queue Create command test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $params = [
            'type' => 'foo',
            'status' => 'bar',
            'entityId' => 1
        ];
        $command = Create::create($params);

        $this->assertEquals('foo', $command->getType());
        $this->assertEquals('bar', $command->getStatus());
        $this->assertEquals(1, $command->getEntityId());
    }
}
