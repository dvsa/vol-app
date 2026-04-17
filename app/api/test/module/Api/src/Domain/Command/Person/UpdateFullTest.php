<?php

declare(strict_types=1);

/**
 * Update Person
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Person;

use Dvsa\Olcs\Api\Domain\Command\Person\UpdateFull;

/**
 * Update Person
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UpdateFullTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = UpdateFull::create(
            [
                'id' => 1,
                'version' => 2,
                'firstName' => 'fn',
                'lastName' => 'ln',
                'title' => 'mr',
                'birthDate' => '2015-01-01',
                'birthPlace' => 'bp'
            ]
        );

        $this->assertEquals(1, $command->getId());
        $this->assertEquals(2, $command->getVersion());
        $this->assertEquals('fn', $command->getFirstName());
        $this->assertEquals('ln', $command->getLastName());
        $this->assertEquals('mr', $command->getTitle());
        $this->assertEquals('2015-01-01', $command->getBirthDate());
        $this->assertEquals('bp', $command->getBirthPlace());
    }
}
