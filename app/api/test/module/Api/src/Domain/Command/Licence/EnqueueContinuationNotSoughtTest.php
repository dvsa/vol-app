<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Licence;

use Dvsa\Olcs\Api\Domain\Command\Licence\EnqueueContinuationNotSought;

/**
 * Enqueue CNS Test
 *
 * @author Alex Peshkov <alex.peshkov@vltech.co.uk>
 */
class EnqueueContinuationNotSoughtTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = EnqueueContinuationNotSought::create(['licences' => 'foo', 'date' => 'bar']);

        $this->assertEquals('foo', $command->getLicences());
        $this->assertEquals('bar', $command->getDate());
    }
}
